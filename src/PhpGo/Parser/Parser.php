<?php declare(strict_types=1);

namespace PhpGo\Parser;

use BadMethodCallException;
use PhpGo\Ast\ArrayType;
use PhpGo\Ast\AssignStmt;
use PhpGo\Ast\BadExpr;
use PhpGo\Ast\BasicLit;
use PhpGo\Ast\BinaryExpr;
use PhpGo\Ast\BlockStmt;
use PhpGo\Ast\CallExpr;
use PhpGo\Ast\DeclarationInterface;
use PhpGo\Ast\ExpressionInterface;
use PhpGo\Ast\ExprStmt;
use PhpGo\Ast\FieldList;
use PhpGo\Ast\FuncDecl;
use PhpGo\Ast\GenDecl;
use PhpGo\Ast\GoObject;
use PhpGo\Ast\Ident;
use PhpGo\Ast\ImportSpec;
use PhpGo\Ast\IndexExpr;
use PhpGo\Ast\LabeledStmt;
use PhpGo\Ast\ObjectKind;
use PhpGo\Ast\ParenExpr;
use PhpGo\Ast\Program;
use PhpGo\Ast\ReturnStatement;
use PhpGo\Ast\Scope;
use PhpGo\Ast\StatementInterface;
use PhpGo\Ast\UnaryExpr;
use PhpGo\Lexer\Lexer;
use PhpGo\Token\EofType;
use PhpGo\Token\EqlType;
use PhpGo\Token\FuncType;
use PhpGo\Token\IdentType;
use PhpGo\Token\ImportType;
use PhpGo\Token\LparenType;
use PhpGo\Token\PeriodType;
use PhpGo\Token\RangeType;
use PhpGo\Token\RparenType;
use PhpGo\Token\StringType;
use PhpGo\Token\Token;
use PhpGo\Token\TokenType;
use UnexpectedValueException;

/**
 * Class Parser
 * @package PhpGo\Parser
 *
 * TODO: Real implementation
 * https://github.com/golang/go/blob/master/src/go/parser/parser.go
 */
final class Parser
{
    private Lexer $lexer;
    private ?Token $curToken;
    private ?Token $peekToken;

    // Non-syntactic parser control
    private int $exprLev;  // < 0: in control clause, >= 0: in expression
    private bool $inRhs; // if set, the parser is parsing a rhs expression

    private ?Scope $topScope;
    private ?Scope $pkgScope;
    /** @var array<GoObject> * */
    private array $unresolved;
    private string $lit; // token literal


    public function __construct(Lexer $l)
    {
        $this->lexer = $l;
        $this->peekToken = null;
        $this->curToken = null;
        $this->exprLev = 0;
        $this->inRhs = false;
        $this->topScope = null;
        $this->pkgScope = null;
        $this->unresolved = [];
        $this->lit = '';

        // initialize $curToken, $peekToken.
        $this->nextToken();
        $this->nextToken();
    }

    public function nextToken(): void
    {
        $this->curToken = $this->peekToken;
        $this->peekToken = $this->lexer->nextToken();
        // TODO: Goのparser.next()にはコメントだったらスキップするような処理が入っている。
    }

    public function parseProgram(): Program
    {
        $statements = [];
        $name = null;
        $this->openScope();
        $this->pkgScope = $this->topScope; // TODO: これであっているのか？普通に渡しても参照わたし？
        while (!$this->curToken->type instanceof EofType) {
            switch ($this->curToken->type->getType()) {
                // FIXME: 本当はファイルの先頭に一回しか現れてはいけない。REPLを考えると、mustで現れるようにもできない。
                case TokenType::T_PACKAGE:
                    // TODO: $program.packageにposition of "package" keywordを保存しておく
                    $this->expect(TokenType::T_PACKAGE);
                    $name = $this->parseIdent();
                    break;
                case TokenType::T_IMPORT:
                    $statements[] = $this->parseImportGenDecl($this->curToken);
                    break;
                // FIXME: 実装中だけ。トップレベルではこない。
                case TokenType::T_RETURN:
                    $statements[] = $this->parseReturnStmt();
                    break;
                case TokenType::T_FUNC:
                    $statements[] = $this->parseFuncDecl();
                    break;
                default:
                    // [StatementInterface, bool]
                    $results = $this->parseSimpleStmt(self::BASIC);
                    $statements[] = $results[0];
            }
            $this->nextToken();
        }
        $this->closeScope();
        // assert(p.topScope == nil, "unbalanced scopes")
        // assert(p.labelScope == nil, "unbalanced label scopes")
        if (!is_null($this->topScope)) {
            throw new UnexpectedValueException('unbalanced scopes');
        }

        $program = new Program($statements);
        if (!is_null($name)) {
            $program->name = $name;
        }
        $program->scope = $this->pkgScope;

        return $program;
    }

    /**
     * @param Token $keyword
     * @return DeclarationInterface go/parser/Parser.parseGenDeclをIMPORTのparse専用に改造したもの。
     *
     * go/parser/Parser.parseGenDeclをIMPORTのparse専用に改造したもの。
     */
    private function parseImportGenDecl(Token $keyword): DeclarationInterface
    {
        // doc := p.leadComment // TODO: parse comment.
        if (!$this->curToken->type instanceof ImportType) {
            throw new \UnexpectedValueException("expected ImportType Token, but {$this->curToken}");
        }
        // var lparen, rparen token.Pos // TODO: save lparen, rparen position.
        $this->nextToken(); // pos := p.expect(keyword) // TODO: Declのポジションをposに保存しつつnextTokenする。
        $list = []; // []ast.Spec
        if ($this->curToken->type instanceof LparenType) {
            // lparen = p.pos // TODO: lparenのposを記憶しておく
            $this->nextToken();
            while (!($this->curToken->type instanceof RparenType) && !($this->curToken->type instanceof EofType)) {
                $list[] = $this->parseImportSpec();
            }
            $this->expect(TokenType::T_RPAREN); // rparen = p.expect(token.RPAREN) // TODO: rparenのposを記憶しておく。
            $this->expectSemi();
        } else {
            $list[] = $this->parseImportSpec();
        }
        return new GenDecl($keyword, $list); // TODO: posやcommentを保存する必要がある。
    }

    private function parseImportSpec(): ImportSpec
    {
        $indent = null;
        switch ($this->curToken->type->getType()) {
            case TokenType::T_PERIOD:
                $ident = new Token(new PeriodType(), '.');
                $this->nextToken();
                break;
            case TokenType::T_IDENT:
                $ident = $this->parseIdent();
        }
        // pos := p.pos
        $path = '';
        if ($this->curToken->type->getType() === TokenType::T_STRING) {
            $path = $this->curToken->literal;
            // TODO: validate import path.
            //  if !isValidImport(path) {
            //    p.error(pos, "invalid import path: "+path)
            //  }
            $this->nextToken();
        } else {
            $this->expect(TokenType::T_STRING); // use expect() error handling
        }
        $this->expectSemi(); // call before accessing p.linecomment

        // collect imports
        $spec = new ImportSpec(new Token(new StringType(), $path), $indent);
        // p.imports = append(p.imports, spec) // TODO: set if build fset.
        return $spec;
    }

    // FIXME: 動作確認ができたらprivateにする
    public function parseReturnStmt(): ReturnStatement
    {
        $this->expect(TokenType::T_RETURN);
        $x = [];
        if ($this->curToken->type->getType() !== TokenType::T_SEMICOLON
            && $this->curToken->type->getType() !== TokenType::T_RBRACE) {
            $x = $this->parseRhsList();
        }
        $this->expectSemi();
        return new ReturnStatement($x);
    }

    private function parseBody(Scope $scope): BlockStmt
    {
        $lbrace = $this->expect(TokenType::T_LBRACE);
        $this->topScope = $scope;
        $this->openLabelScope();
        $list = $this->parseStmtList();
        $this->closeLabelScope();
        $this->closeScope();
        $rbrace = $this->expect2(TokenType::T_RBRACE);

        return new BlockStmt($lbrace, $list, $rbrace);
    }

    /**
     * @return array<StatementInterface>
     */
    private function parseStmtList(): array
    {
        /** @var array<StatementInterface> $list */
        $list = [];
        while ($this->curToken->type->getType() != TokenType::T_CASE
            && $this->curToken->type->getType() != TokenType::T_DEFAULT
            && $this->curToken->type->getType() != TokenType::T_RBRACE
            && $this->curToken->type->getType() != TokenType::T_EOF) {
            $list[] = $this->parseStmt();
        }
        return $list;
    }

    private function parseFuncDecl(): FuncDecl
    {
        // doc := p.leadComment
        $pos = $this->expect(TokenType::T_FUNC); // FuncTypeで使う
        $scope = new Scope($this->topScope);


        /** @var FieldList $recv */
        $recv = null;

        // if p.tok == token.LPAREN {
        //        recv = p.parseParameters(scope, false)
        // }
        //
        $ident = $this->parseIdent();

        // params, results := p.parseSignature(scope)

        // FIXME: skip parameters and results
        $this->expect(TokenType::T_LPAREN);
        $this->expect(TokenType::T_RPAREN);

        $body = null;
        if ($this->curToken->type->getType() == TokenType::T_LBRACE) {
            $body = $this->parseBody($scope);
            $this->expectSemi();
        } elseif ($this->curToken->type->getType() == TokenType::T_SEMICOLON) {
            $this->nextToken();
            if ($this->curToken->type->getType() == TokenType::T_LBRACE) {
                // opening { of function declaration on next line
                echo 'parseFuncDecl: unexpected semicolon or newline before {';
                $body = $this->parseBody($scope);
                $this->expectSemi();
            }
        } else {
            $this->expectSemi();
        }
        $decl = new FuncDecl();
        $decl->body = $body;
        $decl->name = $ident;
        $decl->type = new FuncType();
        if (is_null($recv)) {
            // Go spec: The scope of an identifier denoting a constant, type,
            // variable, or function (but not method) declared at top level
            // (outside any function) is the package block.
            //
            // init() functions cannot be referred to and there may
            // be more than one - don't put them in the pkgScope
            if ($ident->name != "init") {
                // TODO: implement declare.
                // p.declare(decl, nil, p.pkgScope, ast.Fun, ident)
                echo "declare";
            }
        }
        return $decl;
    }

    // ----------------------------------------------------------------------------
    // Common productions

    // If lhs is set, result list elements which are identifiers are not resolved.
    private function parseExprList(bool $lhs): array // array<ast.Expr>
    {
        $list = [];
        $list[] = $this->checkExpr($this->parseExpr($lhs));
        while ($this->curToken->type->getType() == TokenType::T_COMMA) {
            $this->nextToken();
            $list[] = $this->checkExpr($this->parseExpr($lhs));

        }
        return $list;
    }

    /**
     * @return array<ExpressionInterface>
     */
    private function parseLhsList(): array
    {
        $old = $this->inRhs;
        $this->inRhs = false;
        $list = $this->parseExprList(true);
        switch ($this->curToken->type->getType()) {
            case TokenType::T_DEFINE:
                // lhs of a short variable declaration
                // but doesn't enter scope until later:
                // caller must call p.shortVarDecl(p.makeIdentList(list))
                // at appropriate time.
                break;
            case TokenType::T_COLON:
                // lhs of a label declaration or a communication clause of a select
                // statement (parseLhsList is not called when parsing the case clause
                // of a switch statement):
                // - labels are declared by the caller of parseLhsList
                // - for communication clauses, if there is a stand-alone identifier
                //   followed by a colon, we have a syntax error; there is no need
                //   to resolve the identifier in that case
                break;
            default:
                // identifiers must be declared elsewhere
                foreach ($list as $x) {
                    $this->resolve($x);
                }
        }
        $this->inRhs = $old;
        return $list;
    }

    private function parseRhsList(): array // array<ast.Expr>
    {
        $old = $this->inRhs;
        $this->inRhs = true;
        $list = $this->parseExprList(false);
        $this->inRhs = $old;
        return $list;
    }

    // ----------------------------------------------------------------------------
    // Expressions

    /**
     * parseOperand may return an expression or a raw type (incl. array
     * types of the form [...]T. Callers must verify the result.
     * If lhs is set and the result is an identifier, it is not resolved.
     * @param bool $lhs
     * @return ExpressionInterface
     */
    private function parseOperand(bool $lhs): ExpressionInterface
    {
        switch ($this->curToken->type->getType()) {
            case TokenType::T_IDENT:
                $x = $this->parseIdent();
                if (!$lhs) {
                    $this->resolve($x);
                }
                return $x;
            case TokenType::T_STRING:
            case TokenType::T_INT:
                // token.FLOAT, token.IMAG, token.CHAR, token.STRING:
                $x = new BasicLit($this->curToken);
                $this->nextToken();
                return $x;
            // case token.LPAREN:
            //      lparen := p.pos
            //      p.next()
            //      p.exprLev++
            //      x := p.parseRhsOrType() // types may be parenthesized: (some type)
            //      p.exprLev--
            //      rparen := p.expect(token.RPAREN)
            //      return &ast.ParenExpr{Lparen: lparen, X: x, Rparen: rparen}
            // case token.FUNC:
            //      return p.parseFuncTypeOrLit()
            default:
                throw new BadMethodCallException(__METHOD__ . "not implementation {$this->curToken->string()}yet.");
        }
        // if typ := p.tryIdentOrType(); typ != nil {
        //      // could be type for composite literal or conversion
        //      _, isIdent := typ.(*ast.Ident)
        //      assert(!isIdent, "type cannot be identifier")
        //      return typ
        //  }
        //
        //  // we have an error
        //  pos := p.pos
        //  p.errorExpected(pos, "operand")
        //  p.advance(stmtStart)
        //  return &ast.BadExpr{From: pos, To: p.pos}
    }

    /**
     * checkExprOrType checks that x is an expression or a type
     * (and not a raw type such as [...]T).
     *
     * @param ExpressionInterface $x
     * @return ExpressionInterface
     */
    private function checkExprOrType(ExpressionInterface $x): ExpressionInterface
    {
        $t = $this->unparen($x);
        switch (true) {
            case $t instanceof ParenExpr:
                throw  new UnexpectedValueException(__METHOD__ . ": unreachable");
            case $t instanceof ArrayType:
                throw new BadMethodCallException(__METHOD__ . ": not implement for ArrayType yet");
        }
        // all other nodes are expressions or types
        return $x;
    }

    /**
     * If lhs is set and the result is an identifier, it is not resolved.
     *
     * @param bool $lhs
     * @return ExpressionInterface
     */
    private function parsePrimaryExpr(bool $lhs): ExpressionInterface
    {
        $x = $this->parseOperand($lhs);

        while (true) {
            switch ($this->curToken->type->getType()) {
                case TokenType::T_PERIOD:
                case TokenType::T_LBRACK:
                    throw new BadMethodCallException('parsePrimaryExpr is not implementation yet');
                case TokenType::T_LPAREN:
                    if ($lhs) {
                        $this->resolve($x);
                    }
                    $x = $this->parseCallOrConversion($this->checkExprOrType($x));
                    break;
                case TokenType::T_LBRACE:
                    throw new BadMethodCallException('parsePrimaryExpr is not implementation yet');
                default:
                    goto L;
            }
            $lhs = false; // no need to try to resolve again
        }
        L:
        return $x;
    }

    /**
     * @param ExpressionInterface $fun
     * @return CallExpr
     *
     * port from go/ast/Parser.parseCallOrConversion
     */
    private function parseCallOrConversion(ExpressionInterface $fun): CallExpr
    {
        $lparen = $this->expect(TokenType::T_LPAREN);
        $this->exprLev++;
        /** @var array<ExpressionInterface> $list */
        $list = [];
        $ellipsis = 0; // token.Pos
        $rparenType = new RparenType(); // create outside loop for performance.
        // $ellipsis == 0: original is !ellipsis.IsValid()
        while ($this->curToken->type->getType() != TokenType::T_RPAREN
            && $this->curToken->type->getType() != TokenType::T_EOF && $ellipsis == 0) {
            $list[] = $this->parseRhsOrType();// builtins may expect a type: make(some type, ...)
            if ($this->curToken->type->getType() == TokenType::T_ELLIPSIS) {
                throw new BadMethodCallException(__METHOD__ . "is not support ellipsis now");
            }
            if (!$this->atComma("argument list", $rparenType)) {
                break;
            }
            $this->nextToken();
        }
        $this->exprLev--;
        $rparen = $this->expectClosing($rparenType, "argument list");
        return new CallExpr($fun, $lparen, $list, $ellipsis, $rparen);
    }

    /**
     * If lhs is set and the result is an identifier, it is not resolved.
     *
     * @param bool $lhs
     * @return ExpressionInterface
     */
    private function parseUnaryExpr(bool $lhs): ExpressionInterface
    {
        switch ($this->curToken->type->getType()) {
            case TokenType::T_ADD:
            case TokenType::T_SUB:
            case TokenType::T_NOT:
            case TokenType::T_XOR:
            case TokenType::T_AND:
                // pos, op := p.pos, p.tok
                $op = $this->curToken;
                $this->nextToken();
                // x := p.parseUnaryExpr(false)
                // return &ast.UnaryExpr{OpPos: pos, Op: op, X: p.checkExpr(x)}
                throw new BadMethodCallException('parseUnaryExpr is not implementation "ADD", "SUB", etc... yet');
            case TokenType::T_ARROW:
                // channel type or receive expression
                // arrow := p.pos
                // p.next()
                //
                // If the next token is token.CHAN we still don't know if it
                // is a channel type or a receive operation - we only know
                // once we have found the end of the unary expression. There
                // are two cases:
                //
                //   <- type  => (<-type) must be channel type
                //   <- expr  => <-(expr) is a receive from an expression
                //
                // In the first case, the arrow must be re-associated with
                // the channel type parsed already:
                //
                //   <- (chan type)    =>  (<-chan type)
                //   <- (chan<- type)  =>  (<-chan (<-type))
                //
                // x := p.parseUnaryExpr(false)
                //
                // determine which case we have
                // if typ, ok := x.(*ast.ChanType); ok {
                // (<-type)
                //
                // re-associate position info and <-
                // dir := ast.SEND
                // for ok && dir == ast.SEND {
                // if typ.Dir == ast.RECV {
                // error: (<-type) is (<-(<-chan T))
                // p.errorExpected(typ.Arrow, "'chan'")
                // }
                // arrow, typ.Begin, typ.Arrow = typ.Arrow, arrow, arrow
                // dir, typ.Dir = typ.Dir, ast.RECV
                // typ, ok = typ.Value.(*ast.ChanType)
                // }
                // if dir == ast.SEND {
                //    p.errorExpected(arrow, "channel type")
                // }
                //
                // return x
                // }
                //
                // // <-(expr)
                // return &ast.UnaryExpr{OpPos: arrow, Op: token.ARROW, X: p.checkExpr(x)}
                throw new BadMethodCallException('parseUnaryExpr is not implementation "ARROW" yet');
            case TokenType::T_MUL:
                // pointer type or unary "*" expression
                // pos := p.pos
                // p.next()
                // x := p.parseUnaryExpr(false)
                // return &ast.StarExpr{Star: pos, X: p.checkExprOrType(x)}
                throw new BadMethodCallException('parseUnaryExpr is not implementation "MUL" yet');
        }
        return $this->parsePrimaryExpr($lhs);
    }

    /**
     * @return array [Token, int]
     */
    private function tokPrec(): array
    {
        $tok = $this->curToken;
        if ($this->inRhs && $tok->type->getType() == TokenType::T_ASSIGN) {
            $tok = new Token(new EqlType(), '');
        }
        return [$tok, $tok->precedence()];
    }

    /**
     * If lhs is set and the result is an identifier, it is not resolved.
     *
     * @param bool $lhs
     * @param int  $prec1
     * @return ExpressionInterface
     */
    private function parseBinaryExpr(bool $lhs, int $prec1): ExpressionInterface
    {
        $x = $this->parseUnaryExpr($lhs);
        while (true) {
            $arr = $this->tokPrec(); // array [Token, int]
            $op = Token::castToken($arr[0]);
            $oprec = $arr[1];
            if ($oprec < $prec1) {
                return $x;
            }
            $pos = $this->expect($op->type->getType());
            if ($lhs) {
                $this->resolve($x);
                $lhs = false;
            }
            $y = $this->parseBinaryExpr(false, $oprec + 1);
            // x = &ast.BinaryExpr{X: p.checkExpr(x), OpPos: pos, Op: op, Y: p.checkExpr(y)}
            $x = new BinaryExpr($x, $op, $y);
        }
    }

    /**
     * If lhs is set and the result is an identifier, it is not resolved.
     * The result may be a type or even a raw type ([...]int). Callers must
     * check the result (using checkExpr or checkExprOrType), depending on
     * context.
     */
    private function parseExpr(bool $lhs): ExpressionInterface
    {
        return $this->parseBinaryExpr($lhs, Token::LOWEST_PREC + 1);
    }

    private function parseRhs(): ExpressionInterface
    {
        $old = $this->inRhs;
        $this->inRhs = true;
        $x = $this->checkExpr($this->parseExpr(false));
        $this->inRhs = $old;
        return $x;
    }

    /**
     * checkExpr checks that x is an expression (and not a type).
     *
     * @param ExpressionInterface $x
     * @return ExpressionInterface
     */
    private function checkExpr(ExpressionInterface $x): ExpressionInterface
    {
        $x = $this->unparen($x);
        switch (true) {
            case $x instanceof BadExpr:
            case $x instanceof Ident:
            case $x instanceof BasicLit:
                // case *ast.FuncLit:
                // case *ast.CompositeLit:
                break;
            case $x instanceof ParenExpr:
                throw new UnexpectedValueException("checkExpr: unreachable {$x->tokenLiteral()}");
            // case *ast.SelectorExpr:
            case $x instanceof IndexExpr:
                // case *ast.SliceExpr:
                break;
            // case *ast.TypeAssertExpr:
            // If t.Type == nil we have a type assertion of the form
            // y.(type), which is only allowed in type switch expressions.
            // It's hard to exclude those but for the case where we are in
            // a type switch. Instead be lenient and test this in the type
            // checker.
            // break;
            case $x instanceof CallExpr:
                // case *ast.StarExpr:
            case $x instanceof UnaryExpr:
            case $x instanceof BinaryExpr:
                break;
            default:
                // all other nodes are not proper expressions
                // p.errorExpected(x.Pos(), "expression")
                // x = &ast.BadExpr{From: x.Pos(), To: p.safePos(x.End())}
                $x = new BadExpr();
        }
        return $x;
    }

    /**
     * safePos returns a valid file position for a given position: If pos
     * is valid to begin with, safePos returns pos. If pos is out-of-range,
     * safePos returns the EOF position.
     *
     * This is hack to work around "artificial" end positions in the AST which
     * are computed by adding 1 to (presumably valid) token positions. If the
     * token positions are invalid due to parse errors, the resulting end position
     * may be past the file's EOF position, which would lead to panics if used
     * later on.
     *
     * @param int $pos
     * @return int
     *
     */
    private function safePos(int $pos): int
    {
        //        defer func() {
        //if recover() != nil {
        //    res = token.Pos(p.file.Base() + p.file.Size()) // EOF position
        //}
        //}()
        // _ = p.file.Offset(pos) // trigger a panic if position is out-of-range
        return $pos;
    }

    /**
     * If x is of the form (T), unparen returns unparen(T), otherwise it returns x.
     *
     * @param ExpressionInterface $x
     * @return ExpressionInterface
     */
    private function unparen(ExpressionInterface $x): ExpressionInterface
    {
        if ($x instanceof ParenExpr) {
            $p = ParenExpr::castParentExpr($x);
            $x = $this->unparen($p->x);
        }

        return $x;
    }

    // ----------------------------------------------------------------------------
    // Scoping support

    /**
     * If x is an identifier, tryResolve attempts to resolve x by looking up
     * the object it denotes. If no object is found and collectUnresolved is
     * set, x is marked as unresolved and collected in the list of unresolved
     * identifiers.
     *
     * @param ExpressionInterface $x
     * @param bool                $collectUnresolved
     */
    private function tryResolve(ExpressionInterface $x, bool $collectUnresolved): void
    {
        // nothing to do if x is not an identifier or the blank identifier
        if (!($x instanceof Ident)) {
            return;
        }
        $ident = Ident::castIdent($x);
        if ($ident->name === '_') {
            return;
        }

        // try to resolve the identifier
        for ($s = $this->topScope; $s != null; $s = $s->outer) {
            $obj = $s->lookup($ident->name);
            if (!is_null($obj)) {
                $ident->object = $obj;
                return;
            }
        }
        // all local scopes are known, so any unresolved identifier
        // must be found either in the file scope, package scope
        // (perhaps in another file), or universe scope --- collect
        // them so that they can be resolved later
        if ($collectUnresolved) {
            $ident->object = GoObject::unresolovedObject();
            $this->unresolved[] = $ident;
        }
    }

    private function resolve(ExpressionInterface $x): void
    {
        $this->tryResolve($x, true);
    }

    // ----------------------------------------------------------------------------
    // Scoping support

    private function openScope(): void
    {
        $this->topScope = new Scope($this->topScope);
    }

    private function closeScope(): void
    {
        $this->topScope = $this->topScope->outer;
    }

    private function openLabelScope(): void
    {
        // TODO: need to implementation.
        // p.labelScope = ast.NewScope(p.labelScope)
        // p.targetStack = append(p.targetStack, nil)
    }

    private function closeLabelScope(): void
    {
        // TODO: need to implementation.
        // resolve labels
        //n := len(p.targetStack) - 1
        // scope := p.labelScope
        // for _, ident := range p.targetStack[n] {
        //    ident.Obj = scope.Lookup(ident.Name)
        //    if ident.Obj == nil && p.mode&DeclarationErrors != 0 {
        //        p.error(ident.Pos(), fmt.Sprintf("label %s undefined", ident.Name))
        //    }
        //  }
        // pop label scope
        // p.targetStack = p.targetStack[0:n]
        // p.labelScope = p.labelScope.Outer
    }

    /**
     * @param AssignStmt $decl
     * @param array<ExpressionInterface> $list
     */
    private function shortVarDecl(AssignStmt $decl, array $list): void
    {
        // Go spec: A short variable declaration may redeclare variables
        // provided they were originally declared in the same block with
        // the same type, and at least one of the non-blank variables is new.
        $n = 0; // number of new variables
        foreach ($list as $x) {
            if ($x instanceof Ident) {
                $ident = Ident::castIdent($x);
                if ($ident->object != null) {
                    throw new UnexpectedValueException("identifier already declared or resolved");
                }
                $obj = new GoObject(ObjectKind::kindVar(), $ident->name);
                // remember corresponding assignment for other tools
                $obj->decl = $decl;
                $ident->object = $obj;
                if ($ident->name != '_') {
                    $alt = $this->topScope->insert($obj);
                    if (!is_null($alt)) {
                        $ident->object = $alt; // redeclaration
                    } else {
                        $n++; // new declaration
                    }
                }
            } else {
                throw new UnexpectedValueException('shortVarDecl: identifier on left side of :=');
            }
        }
        // if n == 0 && p.mode&DeclarationErrors != 0 {
        if ($n == 0) {
            throw new UnexpectedValueException('shortVarDecl: no new variables on left side of :=');
        }
    }

    /**
     * @return ExpressionInterface
     */
    private function parseRhsOrType(): ExpressionInterface
    {
        $old = $this->inRhs;
        $this->inRhs = true;
        $x = $this->checkExprOrType($this->parseExpr(false));
        $this->inRhs = $old;
        return $x;
    }

    // ----------------------------------------------------------------------------
    // Statements

    // Parsing modes for parseSimpleStmt.
    private const BASIC = 0;
    private const LABEL_OK = 1;
    private const RANGE_OK = 2;

    /**
     * parseSimpleStmt returns true as 2nd result if it parsed the assignment
     * of a range clause (with mode == rangeOk). The returned statement is an
     * assignment with a right-hand side that is a single unary expression of
     * the form "range x". No guarantees are given for the left-hand side.
     *
     * @param int $mode BASIC or LABEL_OK or RANGE_OK
     * @return array [StatementInterface, bool]
     *
     * port from go/parser/Parser.parseSimpleStmt.
     */
    private function parseSimpleStmt(int $mode): array
    {
        $x = $this->parseLhsList();

        // REPL対応
        if ($this->curToken->type->getType() === TokenType::T_SEMICOLON
            && $this->peekToken->type->getType() === TokenType::T_EOF) {
            return [$x[0], false];
        }
        switch ($this->curToken->type->getType()) {
            case TokenType::T_DEFINE:
            case TokenType::T_ASSIGN:
            case TokenType::T_ADD_ASSIGN:
            case TokenType::T_SUB_ASSIGN:
            case TokenType::T_MUL_ASSIGN:
            case TokenType::T_QUO_ASSIGN:
            case TokenType::T_REM_ASSIGN:
            case TokenType::T_AND_ASSIGN:
            case TokenType::T_OR_ASSIGN:
            case TokenType::T_XOR_ASSIGN:
            case TokenType::T_SHL_ASSIGN:
            case TokenType::T_SHR_ASSIGN:
            case TokenType::T_AND_NOT_ASSIGN:
                // assignment statement, possibly part of a range clause
                // TODO: pos, tok := p.pos, p.tok
                $tok = $this->curToken;
                $this->nextToken();
                /** @var array<ExpressionInterface> $y * */
                $y = [];
                $isRange = false;
                if ($mode == self::RANGE_OK && $this->curToken->type->getType() == TokenType::T_RANGE
                    && ($tok->type->getType() == TokenType::T_DEFINE || $tok->type->getType() == TokenType::T_ASSIGN)) {
                    // TODO: pos := p.pos
                    $this->nextToken();
                    // y = []ast.Expr{&ast.UnaryExpr{OpPos: pos, Op: token.RANGE, X: p.parseRhs()}}
                    $y[] = new UnaryExpr(new Token(new RangeType(), ''), $this->parseRhs());
                    $isRange = true;
                } else {
                    $y = $this->parseRhsList();
                }
                $as = new AssignStmt($x, $tok, $y);
                if ($tok->type->getType() == TokenType::T_DEFINE) {
                    $this->shortVarDecl($as, $x);
                }
                return [$as, $isRange];
        }

        if (count($x) > 1) {
            // errorExpected(x[0].Pos(), "1 expression")
            // continue with first expression
            echo 'continue with first expression';
        }
        switch ($this->curToken->type->getType()) {
            case TokenType::T_COLON:
                // labeled statement
                // colon := p.pos
                $this->nextToken();
                // if label, isIdent := x[0].(*ast.Ident); mode == labelOk && isIdent {
                //   // Go spec: The scope of a label is the body of the function
                //   // in which it is declared and excludes the body of any nested
                //   // function.
                //   stmt := &ast.LabeledStmt{Label: label, Colon: colon, Stmt: p.parseStmt()}
                //   p.declare(stmt, nil, p.labelScope, ast.Lbl, label)
                //   return stmt, false
                // }
                // The label declaration typically starts at x[0].Pos(), but the label
                // declaration may be erroneous due to a token after that position (and
                // before the ':'). If SpuriousErrors is not set, the (only) error
                // reported for the line is the illegal label error instead of the token
                // before the ':' that caused the problem. Thus, use the (latest) colon
                // position for error reporting.
                // p.error(colon, "illegal label declaration")
                // return &ast.BadStmt{From: x[0].Pos(), To: colon + 1}, false
                throw new BadMethodCallException("parseSimpleStmt: not implementation COLON yet.");
            case TokenType::T_ARROW:
                // send statement
                // arrow := p.pos
                // p.next()
                // y := p.parseRhs()
                // return &ast.SendStmt{Chan: x[0], Arrow: arrow, Value: y}, false
                throw new BadMethodCallException("parseSimpleStmt: not implementation ALLOW yet.");
            case TokenType::T_INC:
            case TokenType::T_DEC:
                // increment or decrement
                // s := &ast.IncDecStmt{X: x[0], TokPos: p.pos, Tok: p.tok}
                // p.next()
                // return s, false
                throw new BadMethodCallException("parseSimpleStmt: not implementation INC or DEC yet.");
        }
        // expression
        $expr = new ExprStmt($x[0]);
        return [$expr, false];
    }

    /**
     * @return StatementInterface
     *
     * port go/parser/Parser.parseStmt
     */
    private function parseStmt(): StatementInterface
    {
        $s = null;
        switch ($this->curToken->type->getType()) {
            case TokenType::T_CONST:
            case TokenType::T_TYPE:
            case TokenType::T_VAR:
                // s = &ast.DeclStmt{Decl: p.parseDecl(stmtStart)}
                break;
            // tokens that may start an expression
            case TokenType::T_IDENT:
            case TokenType::T_INT:
            case TokenType::T_FLOAT:
            case TokenType::T_IMAG:
            case TokenType::T_CHAR:
            case TokenType::T_STRING:
            case TokenType::T_FUNC:
            case TokenType::T_LPAREN: // operands
            case TokenType::T_LBRACK:
            case TokenType::T_STRUCT:
            case TokenType::T_MAP:
            case TokenType::T_CHAN:
            case TokenType::T_INTERFACE: // composite types
            case TokenType::T_ADD:
            case TokenType::T_SUB:
            case TokenType::T_MUL:
            case TokenType::T_AND:
            case TokenType::T_XOR:
            case TokenType::T_ARROW:
            case TokenType::T_NOT: // unary operators
                $result = $this->parseSimpleStmt(self::LABEL_OK);
                $s = $result[0];
                // because of the required look-ahead, labeled statements are
                // parsed by parseSimpleStmt - don't expect a semicolon after
                // them

                if (!($s instanceof LabeledStmt)) {
                    $this->expectSemi();
                }
                break;
            case TokenType::T_GO:
                throw new BadMethodCallException('parseStmt: not implement T_GO yet');
            case TokenType::T_DEFER:
                throw new BadMethodCallException('parseStmt: not implement T_DEFER yet');
            case TokenType::T_RETURN:
                // s = p.parseReturnStmt()
                throw new BadMethodCallException('parseStmt: not implement T_RETURN yet');
            case TokenType::T_BREAK:
            case TokenType::T_CONTINUE:
            case TokenType::T_GOTO:
            case TokenType::T_FALLTHROUGH:
                throw new BadMethodCallException('parseStmt: not implement break, continue, goto, fallthrough yet');
            case TokenType::T_LBRACE:
                throw new BadMethodCallException('parseStmt: not implement "{" yet');
            case TokenType::T_IF:
                throw new BadMethodCallException('parseStmt: not implement "if" yet');
            case TokenType::T_SWITCH:
                throw new BadMethodCallException('parseStmt: not implement "switch" yet');
            case TokenType::T_SELECT:
                throw new BadMethodCallException('parseStmt: not implement "select" yet');
            case TokenType::T_FOR:
                throw new BadMethodCallException('parseStmt: not implement "for" yet');
            case TokenType::T_SEMICOLON:
                throw new BadMethodCallException('parseStmt: not implement ";" yet');
            case TokenType::T_RBRACE:
                throw new BadMethodCallException('parseStmt: not implement "}" yet');
            default:
                // no statement found
                // pos := p.pos
                // p.errorExpected(pos, "statement")
                // p.advance(stmtStart)
                // s = &ast.BadStmt{From: pos, To: p.pos}
                throw new UnexpectedValueException('no statement found');
        }

        return $s;
    }

    /**
     * port from go/parser/Parser.expectSemi.
     */
    private function expectSemi(): void
    {
        // semicolon is optional before a closing ')' or '}'
        if ($this->curToken->type->getType() !== TokenType::T_RPAREN
            && $this->curToken->type->getType() !== TokenType::T_RBRACE) {
            switch ($this->curToken->type->getType()) {
                case TokenType::T_COMMA:
                    // permit a ',' instead of a ';' but complain
                    throw new UnexpectedValueException('expected ";", but ","');
                case TokenType::T_SEMICOLON:
                    $this->nextToken();
                    break;
                default:
                    throw new BadMethodCallException('expected ";", actual ' . $this->curToken->string());
                // p.advance(stmtStart) // TODO: implement advance method.
            }
        }
    }

    private function atComma(string $context, TokenType $follow): bool
    {
        if ($this->curToken->type->getType() == TokenType::T_COMMA) {
            return true;
        }
        if ($this->curToken->type->getType() != $follow->getType()) {
            $msg = "missing ','";
            if ($this->curToken->type->getType() == TokenType::T_SEMICOLON && $this->lit == "\n") {
                $msg .= " before newline";
            }
            echo "{$msg} in {$context}";
            // TODO: 本当はp.error
            throw new UnexpectedValueException("{$msg} in {$context}");
            return true; // "insert" comma and continue
        }
        return false;
    }

    /**
     * @return Ident
     *
     * port from go/parser/Parser.parseIdent.
     */
    private function parseIdent(): Ident
    {
        // pos := p.pos
        $name = "_";
        if ($this->curToken->type instanceof IdentType) {
            $name = $this->curToken->literal;
            $this->nextToken();
        } else {
            $this->expect(TokenType::T_IDENT);
        }
        return new Ident($name); // TODO: Identにpos情報を保存しておく。
    }

    /**
     * @param string $tokType TokenTypeのconst定数
     * @return int TODO: 現状は常に0。
     *
     * port from go/parser/Parser.expect.
     */
    private function expect(string $tokType): int
    {
        // pos := p.pos // TODO: 位置情報を戻り値に設定する。
        if ($this->curToken->type->getType() !== $tokType) { // p.tok != tok
            throw new UnexpectedValueException("expect {$tokType}, but {$this->curToken->string()}");
        }
        $this->nextToken();
        return 0; // return pos
    }

    /**
     * expect2 is like expect, but it returns an invalid position
     * if the expected token is not found.
     *
     * @param string $type
     * @return int
     */
    private function expect2(string $type): int
    {
        $pos = -1;
        if ($this->curToken->type->getType() == $type) {
            // pos = p.pos
            $pos = -1;
        } else {
            throw new UnexpectedValueException("expect2: expected {$type}, but {$this->curToken->string()}");
        }
        $this->nextToken(); // make progress
        return $pos;
    }

    /**
     * expectClosing is like expect but provides a better error message
     * for the common case of a missing comma before a newline.
     * @param TokenType $tok Goの実装ではToken
     * @param string    $context
     * @return int
     */
    private function expectClosing(TokenType $tok, string $context): int
    {
        if ($this->curToken->type->getType() != $tok->getType() && $this->lit == "\n") {
            // TODO: 本当はp.error
            echo "missing ',' before newline in {$context}";
            $this->nextToken();
        }

        return $this->expect($tok->getType());
    }
}

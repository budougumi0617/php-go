<?php declare(strict_types=1);

namespace PhpGo\Parser;

use PhpGo\Ast\DeclarationInterface;
use PhpGo\Ast\ExpressionInterface;
use PhpGo\Ast\GenDecl;
use PhpGo\Ast\GoObject;
use PhpGo\Ast\Ident;
use PhpGo\Ast\ImportSpec;
use PhpGo\Ast\ParenExpr;
use PhpGo\Ast\Program;
use PhpGo\Ast\ReturnStatement;
use PhpGo\Ast\Scope;
use PhpGo\Ast\StatementInterface;
use PhpGo\Lexer\Lexer;
use PhpGo\Token\EofType;
use PhpGo\Token\IdentType;
use PhpGo\Token\ImportType;
use PhpGo\Token\LparenType;
use PhpGo\Token\PeriodType;
use PhpGo\Token\RparenType;
use PhpGo\Token\StringType;
use PhpGo\Token\Token;
use PhpGo\Token\TokenType;

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
    private bool $inRhs; // if set, the parser is parsing a rhs expression
    private ?Scope $topScope;
    /** @var array<GoObject> * */
    private array $unresolved;


    public function __construct(Lexer $l)
    {
        $this->lexer = $l;
        $this->peekToken = null;
        $this->curToken = null;
        $this->inRhs = false;
        $this->topScope = null;
        $this->unresolved = [];

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
        while (!$this->curToken->type instanceof EofType) {
            switch ($this->curToken->type->getType()) {
                // FIXME: 本当はファイルの戦闘に一回しか現れてはいけない。REPLを考えると、mustで現れるようにもできない。
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
                    $statements[] = $this->parseReturnStmt($this->curToken);
                    break;
            }
            $this->nextToken();
        }

        $program = new Program($statements);
        $program->name = $name;
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
        return new ReturnStatement(x);
    }

    // ----------------------------------------------------------------------------
    // Common productions

    // If lhs is set, result list elements which are identifiers are not resolved.
    private function parseExprList(bool $lhs): array // array<ast.Expr>
    {
        $list = [];
        //	list = append(list, p.checkExpr(p.parseExpr(lhs)))
        //	for p.tok == token.COMMA {
        //		p.next()
        //		list = append(list, p.checkExpr(p.parseExpr(lhs)))
        //	}
        return $list;
    }

    private function parseLhsList(): array
    {
        $old = $this->inRhs;
        $this->inRhs = false;
        $list = $this->parseExprList();
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


    // // checkExpr checks that x is an expression (and not a type).
    //func (p *parser) checkExpr(x ast.Expr) ast.Expr {
    //	switch unparen(x).(type) {
    //	case *ast.BadExpr:
    //	case *ast.Ident:
    //	case *ast.BasicLit:
    //	case *ast.FuncLit:
    //	case *ast.CompositeLit:
    //	case *ast.ParenExpr:
    //		panic("unreachable")
    //	case *ast.SelectorExpr:
    //	case *ast.IndexExpr:
    //	case *ast.SliceExpr:
    //	case *ast.TypeAssertExpr:
    //		// If t.Type == nil we have a type assertion of the form
    //		// y.(type), which is only allowed in type switch expressions.
    //		// It's hard to exclude those but for the case where we are in
    //		// a type switch. Instead be lenient and test this in the type
    //		// checker.
    //	case *ast.CallExpr:
    //	case *ast.StarExpr:
    //	case *ast.UnaryExpr:
    //	case *ast.BinaryExpr:
    //	default:
    //		// all other nodes are not proper expressions
    //		p.errorExpected(x.Pos(), "expression")
    //		x = &ast.BadExpr{From: x.Pos(), To: p.safePos(x.End())}
    //	}
    //	return x
    //}

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
     * @param bool $collectUnresolved
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
    // Statements

    /**
     *
     * parseSimpleStmt returns true as 2nd result if it parsed the assignment
     * of a range clause (with mode == rangeOk). The returned statement is an
     * assignment with a right-hand side that is a single unary expression of
     * the form "range x". No guarantees are given for the left-hand side.
     * @param int $mode
     * @return StatementInterface
     *
     *  port from go/parser/Parser.parseSimpleStmt.
     */
    private function parseSimpleStmt(int $mode): StatementInterface
    {
        return null;
    }
    //func (p *parser) parseSimpleStmt(mode int) (ast.Stmt, bool) {
    //	if p.trace {
    //		defer un(trace(p, "SimpleStmt"))
    //	}
    //
    //	x := p.parseLhsList()
    //
    //	switch p.tok {
    //	case
    //		token.DEFINE, token.ASSIGN, token.ADD_ASSIGN,
    //		token.SUB_ASSIGN, token.MUL_ASSIGN, token.QUO_ASSIGN,
    //		token.REM_ASSIGN, token.AND_ASSIGN, token.OR_ASSIGN,
    //		token.XOR_ASSIGN, token.SHL_ASSIGN, token.SHR_ASSIGN, token.AND_NOT_ASSIGN:
    //		// assignment statement, possibly part of a range clause
    //		pos, tok := p.pos, p.tok
    //		p.next()
    //		var y []ast.Expr
    //		isRange := false
    //		if mode == rangeOk && p.tok == token.RANGE && (tok == token.DEFINE || tok == token.ASSIGN) {
    //			pos := p.pos
    //			p.next()
    //			y = []ast.Expr{&ast.UnaryExpr{OpPos: pos, Op: token.RANGE, X: p.parseRhs()}}
    //			isRange = true
    //		} else {
    //			y = p.parseRhsList()
    //		}
    //		as := &ast.AssignStmt{Lhs: x, TokPos: pos, Tok: tok, Rhs: y}
    //		if tok == token.DEFINE {
    //			p.shortVarDecl(as, x)
    //		}
    //		return as, isRange
    //	}
    //
    //	if len(x) > 1 {
    //		p.errorExpected(x[0].Pos(), "1 expression")
    //		// continue with first expression
    //	}
    //
    //	switch p.tok {
    //	case token.COLON:
    //		// labeled statement
    //		colon := p.pos
    //		p.next()
    //		if label, isIdent := x[0].(*ast.Ident); mode == labelOk && isIdent {
    //			// Go spec: The scope of a label is the body of the function
    //			// in which it is declared and excludes the body of any nested
    //			// function.
    //			stmt := &ast.LabeledStmt{Label: label, Colon: colon, Stmt: p.parseStmt()}
    //			p.declare(stmt, nil, p.labelScope, ast.Lbl, label)
    //			return stmt, false
    //		}
    //		// The label declaration typically starts at x[0].Pos(), but the label
    //		// declaration may be erroneous due to a token after that position (and
    //		// before the ':'). If SpuriousErrors is not set, the (only) error
    //		// reported for the line is the illegal label error instead of the token
    //		// before the ':' that caused the problem. Thus, use the (latest) colon
    //		// position for error reporting.
    //		p.error(colon, "illegal label declaration")
    //		return &ast.BadStmt{From: x[0].Pos(), To: colon + 1}, false
    //
    //	case token.ARROW:
    //		// send statement
    //		arrow := p.pos
    //		p.next()
    //		y := p.parseRhs()
    //		return &ast.SendStmt{Chan: x[0], Arrow: arrow, Value: y}, false
    //
    //	case token.INC, token.DEC:
    //		// increment or decrement
    //		s := &ast.IncDecStmt{X: x[0], TokPos: p.pos, Tok: p.tok}
    //		p.next()
    //		return s, false
    //	}
    //
    //	// expression
    //	return &ast.ExprStmt{X: x[0]}, false
    //}

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
                    throw new \UnexpectedValueException('expected ";"');
                case TokenType::T_SEMICOLON:
                    $this->nextToken();
                    break;
                default:
                    throw new \UnexpectedValueException('expected ";"');
                // p.advance(stmtStart) // TODO: implement advance method.
            }
        }
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
            throw new \UnexpectedValueException("expect {$tokType}, but {$this->curToken->string()}");
        }
        $this->nextToken();
        return 0; // return pos
    }
}

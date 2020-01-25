<?php declare(strict_types=1);

namespace PhpGo\Evaluator;

use InvalidArgumentException;
use PhpGo\Ast\AssignStmt;
use PhpGo\Ast\BasicLit;
use PhpGo\Ast\BinaryExpr;
use PhpGo\Ast\BlockStmt;
use PhpGo\Ast\CallExpr;
use PhpGo\Ast\ExpressionInterface;
use PhpGo\Ast\Field;
use PhpGo\Ast\FuncDecl;
use PhpGo\Ast\Ident;
use PhpGo\Ast\NodeInterface;
use PhpGo\Ast\PrintlnExpr;
use PhpGo\Ast\Program;
use PhpGo\Object\FunctionObject;
use PhpGo\Object\GoObject;
use PhpGo\Object\Integer;
use PhpGo\Object\Nil;
use PhpGo\Object\ObjectType;
use PhpGo\Object\ReturnObj;
use PhpGo\Object\Scope;
use PhpGo\Token\TokenType;
use UnexpectedValueException;

final class Evaluator
{

    private static string $EMBEDDED_PRINTLN = 'println';

    /**
     * println(x)関数を登録する。
     *
     * @param Scope $root 関数を登録する空間
     */
    public static function presetPrint(Scope $root): void
    {
        $x = new Ident('x');
        // FIXME: 可変長を受け付ける
        $params = [$x];
        $stmt = new BlockStmt(0, [
            new PrintlnExpr($x->name),
        ], 0);
        $root->set(self::$EMBEDDED_PRINTLN, new FunctionObject($params, $stmt, $root));
    }

    public static function eval(NodeInterface $node, Scope $scope): ?GoObject
    {
        // FIXME: if-elseはやめたい…
        if ($node instanceof Program) {
            // FIXME pkg scopeを渡せる
            $program = Program::castProgram($node);
            return self::evalStatements($program->statements, $scope);
        } else if ($node instanceof PrintlnExpr) {
            $pe = PrintlnExpr::castPrintlnExpr($node);
            $val = $scope->get($pe->argName);
            echo '(println)    ' . $val->inspect() . PHP_EOL;
            return new Nil();
        } else if ($node instanceof BlockStmt) {
            $blockStmt = BlockStmt::castBlockStmt($node);
            return self::evalBlockStatement($blockStmt, $scope);
        } else if ($node instanceof Ident) {
            $ident = Ident::castIdent($node);
            return self::evalIdent($ident, $scope);
        } else if ($node instanceof BasicLit) {
            $basicLit = BasicLit::castBasicLit($node);
            switch ($basicLit->kind->type->getType()) {
                case TokenType::T_INT:
                    return new Integer(intval($basicLit->value));
                default:
                    throw new InvalidArgumentException("{$basicLit} is not instance of BasicLit");
            }
        } else if ($node instanceof BinaryExpr) {
            $binaryExpr = BinaryExpr::castBinaryExpr($node);
            $x = self::eval($binaryExpr->x, $scope);
            $y = self::eval($binaryExpr->y, $scope);
            switch ($binaryExpr->op->type->getType()) {
                case TokenType::T_ADD:
                    // FIXME: 型キャストせずにvalue呼んでいる。
                    return new Integer(intval($x->value) + intval($y->value));
                default:
                    throw new InvalidArgumentException("{$binaryExpr} supports only ADD");
            }
        } else if ($node instanceof AssignStmt) {
            $stmt = AssignStmt::castAssignStmt($node);
            if ($stmt->tok->type->getType() === TokenType::T_DEFINE) {
                return self::evalDefine($stmt, $scope);
            } else {
                throw new UnexpectedValueException("{$stmt->tok} is not support assign type");
            }
        } else if ($node instanceof FuncDecl) {
            $func = FuncDecl::castFuncDecl($node);
            /** @var array<BasicLit> $params */
            $params = [];
            // $func->recvには、(x, y int, s string)のような情報が含まれている。
            foreach ($func->recv->list as $f) {
                $field = Field::castField($f);
                // x, y int みたいな引数をパースしている。
                foreach ($field->names as $name) {
                    // FIXME: 型情報が落ちている
                    $ident = Ident::castIdent($name);
                    $params[] = $ident;
                }
            }
            $funcObj = new FunctionObject($params, $func->body, $scope);
            if (strlen($func->name->name) > 0) {
                $scope->set($func->name->name, $funcObj);
            }
            return $funcObj;
        } else if ($node instanceof CallExpr) {
            $callExpr = CallExpr::castCallExpr($node);
            $function = self::eval($callExpr->fun, $scope);
            $args = self::evalExpressions($callExpr->args, $scope);
            return self::applyFunction($function, $args);
        } else {
            throw new UnexpectedValueException("{$node} is not support node type");
        }
        return null;
    }

    private static function evalStatements(array $stmts, Scope $scope): GoObject
    {
        $result = null;

        foreach ($stmts as $stmt) {
            $result = self::eval($stmt, $scope);
        }
        return $result;
    }

    private static function evalDefine(AssignStmt $stmt, Scope $scope): GoObject
    {
        $result = null;
        foreach ($stmt->lhs as $i => $lh) {
            $result = self::createGoObject($stmt->rhs[$i]);
            $ident = Ident::castIdent($lh);
            $scope->set($ident->name, $result);
        }

        return $result; // 複数定義だった場合でも、最後に評価した変数の値だけ。
    }

    private static function createGoObject(ExpressionInterface $expr): GoObject
    {
        if ($expr instanceof BasicLit) {
            $basicLit = BasicLit::castBasicLit($expr);
            switch ($basicLit->kind->type->getType()) {
                case TokenType::T_INT:
                    return new Integer(intval($basicLit->value));
                default:
                    throw new InvalidArgumentException("{$basicLit} is not type of BasicLit");
            }
        }

        // FIXME: 束縛関数などが来る（そもそもBasicLitじゃない）可能性もある。
        throw new UnexpectedValueException("createToken: {$expr} is not support type");
    }

    /**
     * @param array<ExpressionInterface> $args
     * @param Scope                      $scope
     *
     * @return array<GoObject>
     */
    private static function evalExpressions(array $args, Scope $scope): array
    {
        $results = [];
        foreach ($args as $arg) {
            $results[] = self::eval($arg, $scope);
        }
        return $results;
    }

    /**
     * @param GoObject        $func
     * @param array<GoObject> $args
     *
     * @return GoObject
     */
    private static function applyFunction(GoObject $func, array $args): GoObject
    {
        $fn = FunctionObject::castFunctionObject($func);
        $funcScope = self::extendScope($fn, $args);
        $evaluated = self::eval($fn->body, $funcScope);
        if ($evaluated instanceof ReturnObj) {
            $ret = ReturnObj::castReturnObj($evaluated);
            return $ret->value;
        }
        return $evaluated;
    }

    private static function extendScope(FunctionObject $func, array $args): Scope
    {
        $exScope = new Scope($func->scope);
        foreach ($func->parameters as $i => $param) {
            $ident = Ident::castIdent($param);
            $exScope->set($ident->name, $args[$i]);
        }
        return $exScope;
    }

    /**
     * @param BlockStmt $stmts
     * @param Scope     $scope
     *
     * @return GoObject
     */
    private static function evalBlockStatement(BlockStmt $stmts, Scope $scope): GoObject
    {
        $result = new Nil();
        foreach ($stmts->list as $stmt) {
            $result = self::eval($stmt, $scope);
            if (is_null($result) && $result->type()->getString() === ObjectType::RETURN_OBJ()->getString()) {
                return $result;
            }
        }
        return $result;
    }


    private static function evalIdent(Ident $ident, Scope $scope): GoObject
    {
        // FIXME: 本当はexception catchしてnewErrorかも
        return $scope->get($ident->name);
    }
}

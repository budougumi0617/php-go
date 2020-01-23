<?php declare(strict_types=1);

namespace PhpGo\Evaluator;

use InvalidArgumentException;
use PhpGo\Ast\AssignStmt;
use PhpGo\Ast\BasicLit;
use PhpGo\Ast\BinaryExpr;
use PhpGo\Ast\ExpressionInterface;
use PhpGo\Ast\Ident;
use PhpGo\Ast\NodeInterface;
use PhpGo\Ast\Program;
use PhpGo\Object\GoObject;
use PhpGo\Object\Integer;
use PhpGo\Object\Scope;
use PhpGo\Token\DefineType;
use PhpGo\Token\IntType;
use PhpGo\Token\StringType;
use PhpGo\Token\Token;
use PhpGo\Token\TokenType;
use UnexpectedValueException;

final class Evaluator
{
    public static function eval(NodeInterface $node, Scope $scope): ?GoObject
    {
        // FIXME: if-elseはやめたい…
        if ($node instanceof Program) {
            // FIXME pkg scopeを渡せる
            $program = Program::castProgram($node);
            return self::evalStatements($program->statements, $scope);
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

    private static function evalIdent(Ident $ident, Scope $scope): GoObject
    {
        // FIXME: 本当はexception catchしてnewErrorかも
        return $scope->get($ident->name);
    }
}

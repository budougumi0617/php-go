<?php declare(strict_types=1);

namespace PhpGo\Evaluator;

use InvalidArgumentException;
use PhpGo\Ast\BasicLit;
use PhpGo\Ast\BinaryExpr;
use PhpGo\Ast\NodeInterface;
use PhpGo\Ast\Program;
use PhpGo\Object\GoObject;
use PhpGo\Object\Integer;
use PhpGo\Token\TokenType;

final class Evaluator
{
    public static function eval(NodeInterface $node): ?GoObject
    {
        // FIXME: if-elseはやめたい…
        if ($node instanceof Program) {
            // FIXME pkg scopeを渡せる
            $program = Program::castProgram($node);
            return self::evalStatements($program->statements);
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
            $x = self::eval($binaryExpr->x);
            $y = self::eval($binaryExpr->y);
            switch ($binaryExpr->op->type->getType()) {
                case TokenType::T_ADD:
                    // FIXME: 型キャストせずにvalue呼んでいる。
                    return new Integer(intval($x->value) + intval($y->value));
                default:
                    throw new InvalidArgumentException("{$binaryExpr} supports only ADD");
            }
        }
        return null;
    }

    private static function evalStatements(array $stmts): GoObject
    {
        $result = null;

        foreach ($stmts as $stmt) {
            $result = self::eval($stmt);
        }
        return $result;
    }
}

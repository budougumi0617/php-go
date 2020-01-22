<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;
use PhpGo\Token\Token;

/**
 * Class BinaryExpr
 * @package PhpGo\Ast
 *
 * port go/ast/BinaryExpr
 */
final class BinaryExpr implements ExpressionInterface
{
    public ExpressionInterface $x; // left operand
    public int $opPos; // position of Op
    public Token $op; // operator
    public ExpressionInterface $y; // right operand

    public function __construct(ExpressionInterface $x, Token $op, ExpressionInterface $y)
    {
        $this->x = $x;
        $this->opPos = 0;
        $this->op = $op;
        $this->y = $y;
    }


    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }

    public static function castBinaryExpr(NodeInterface $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of BinaryExpr");
        }
        return $obj;
    }
}
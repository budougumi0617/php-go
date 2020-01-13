<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Token\Token;

/**
 * Class UnaryExpr
 * @package PhpGo\Ast
 *
 * port go/ast/UnaryExpr
 */
final class UnaryExpr implements ExpressionInterface
{
    public int $opPos; // position of Op
    public Token $op; // operator
    public ExpressionInterface $x; // operand

    public function __construct(Token $op, ExpressionInterface $x)
    {
        $this->opPos = 0;
        $this->op = $op;
        $this->x = $x;
    }

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }
}
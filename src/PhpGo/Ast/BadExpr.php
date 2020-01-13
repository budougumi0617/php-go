<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class BadExpr
 * @package PhpGo\Ast
 *
 * port from go/ast/BadExpr
 */
final class BadExpr implements ExpressionInterface
{
    // From, To token.Pos // position range of bad expression

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }
}
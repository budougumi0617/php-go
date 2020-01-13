<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class IndexExpr
 * @package PhpGo\Ast
 *
 * port from go/ast/IndexExpr
 */
final class IndexExpr implements ExpressionInterface
{
    public ExpressionInterface $x; // expression
    public ExpressionInterface $index; // index expression
    // Lbrack token.Pos // position of "["
    // Rbrack token.Pos // position of "]"

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }
}
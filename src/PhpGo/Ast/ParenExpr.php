<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;

/**
 * Class ParenExpr
 * @package PhpGo\Ast
 *
 * port from go/ast/ParenExpr
 */
final class ParenExpr implements ExpressionInterface
{
    // Lparen token.Pos // position of "("

    public ExpressionInterface $x; // parenthesized expression

    // Rparen token.Pos // position of ")"

    public function __construct(ExpressionInterface $x)
    {
        $this->x = $x;
    }

    public function tokenLiteral(): string
    {
        return "( {$this->x->tokenLiteral()} )";
    }

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public static function castParentExpr(ExpressionInterface $obj): ParenExpr
    {
        if (!($obj instanceof ParenExpr)) {
            throw new InvalidArgumentException("{$obj} is not instance of ParenExpr");
        }
        return $obj;
    }
}
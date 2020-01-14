<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class ArrayTypes
 * @package PhpGo\Ast
 *
 * port from go/ast/ArrayType
 */
final class ArrayType implements ExpressionInterface
{
    // type ArrayType struct {
    //    Lbrack token.Pos // position of "["
    //    Len    Expr      // Ellipsis node for [...]T array types, nil for slice types
    //    Elt    Expr      // element type
    //}

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }
}
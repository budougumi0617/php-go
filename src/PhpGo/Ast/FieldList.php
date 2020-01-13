<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class FieldList
 * @package PhpGo\Ast
 *
 * TODO: implement go/ast/FieldList
 */
final class FieldList implements NodeInterface
{
    // Opening token.Pos // position of opening parenthesis/brace, if any
    // List    []*Field  // field list; or nil
    // Closing token.Pos // position of closing parenthesis/brace, if any

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }
}
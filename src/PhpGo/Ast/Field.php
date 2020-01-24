<?php declare(strict_types=1);


namespace PhpGo\Ast;


final class Field implements NodeInterface
{
    // Comment *CommentGroup // line comments; or nil
    // Doc     *CommentGroup // associated documentation; or nil

    /** @var array<Ident> $names */
    public array $names;              // field/method/parameter names; or nil
    public ExpressionInterface $type; // field/method/parameter type
    public BasicLit $tag;             // field tag; or nil

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }
}
<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Token\Token;
use PhpGo\Token\TokenType;

/**
 * Class ImportSpec
 * @package PhpGo\Ast
 *
 * https://godoc.org/go/ast#ImportSpec
 */
final class ImportSpec implements SpecInterface
{
    // Doc     *CommentGroup // associated documentation; or nil
    // Comment *CommentGroup // line comments; or nil
    public ?Ident $name; // local package name (including "."); or nil
    public BasicLit $path; // import path
    public int $endPos; // end of spec (overrides Path.Pos if nonzero)

    /**
     * ImportSpec constructor.
     * @param Token $tok import path.
     * @param Ident|null $name local package name if setting.
     */
    public function __construct(Token $tok, Ident $name = null)
    {
        $this->path = new BasicLit($tok);
        $this->name = $name;
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
        return '';
    }

    public function specNode(): void
    {
        // TODO: Implement specNode() method.
    }
}
<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Token\Token;

/**
 * Class GenDecl
 * @package PhpGo\Ast
 *
 * https://godoc.org/go/ast#GenDecl
 */
final class GenDecl implements DeclarationInterface
{
    // Doc    *CommentGroup // associated documentation; or nil
    // TokPos token.Pos     // position of Tok
    // Lparen token.Pos     // position of '(', if any
    // Rparen token.Pos // position of ')', if any
    public Token $token; // IMPORT, CONST, TYPE, VAR
    public array $specs; // array(SpecInterface)

    public function __construct(Token $token, array $specs)
    {
        $this->token = $token;
        $this->specs = $specs;
    }

    public function declNode(): void
    {
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
        return '';
    }

}
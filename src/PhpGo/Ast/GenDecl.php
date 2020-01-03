<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Token\Token;

final class GenDecl implements DeclarationInterface
{
    public Token $token;

    public function declNode(): void
    {
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }

}
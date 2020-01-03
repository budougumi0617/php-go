<?php declare(strict_types=1);

namespace PhpGo\Ast;

interface DeclarationInterface extends NodeInterface
{
    public function declNode(): void;
}
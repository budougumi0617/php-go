<?php declare(strict_types=1);

namespace PhpGo\Ast;

interface SpecInterface extends NodeInterface
{
    public function specNode(): void;
}
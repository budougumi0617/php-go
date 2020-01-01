<?php declare(strict_types=1);

namespace PhpGo\Ast;

interface NodeInterface
{
    public function tokenLiteral(): string;
    // Go本体にはある。
    // public function pos(): int;
    // public function end(): int;
}

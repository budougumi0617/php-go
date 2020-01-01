<?php declare(strict_types=1);

namespace PhpGo\Ast;

final class Program implements NodeInterface
{
    public array $statements; // StatementInterface array.

    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }

    public function tokenLiteral(): string
    {
        if (count($this->statements) > 0 && $this->statements[0] instanceof StatementInterface) {
            $stmt = $this->statements[0];
            return $stmt->tokenLiteral();
        }
        return '';
    }
}

<?php declare(strict_types=1);

namespace PhpGo\Ast;

final class ReturnStatement implements StatementInterface
{
    public int $return; // TODO: position of "return" keyword
    public array $results; // array<ExpressionInterface>. result expressions; or nil

    public function __construct(array $results)
    {
        // TODO: set $return
        $this->results = $results;
    }

    public function tokenLiteral(): string
    {
        return '';
    }

    public function stmtNode(): void
    {
        // TODO: Implement stmtNode() method.
    }
}
<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class LabeledStmt
 * @package PhpGo\Ast
 *
 * port go/ast/LabeledStmt
 */
final class LabeledStmt implements StatementInterface
{
    public Ident $label;
    public int $colon; // position of ":"
    public StatementInterface $stmt;

    public function tokenLiteral(): string
    {
        return "{$this->label->name}:\n{$this->stmt->tokenLiteral()}";
    }

    public function stmtNode(): void
    {
        // TODO: Implement stmtNode() method.
    }
}
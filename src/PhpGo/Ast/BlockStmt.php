<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class BlockStmt
 * @package PhpGo\Ast
 *
 * port from go/ast/BlockStmt
 */
final class BlockStmt implements StatementInterface
{
    public int $lbrace; // position of "{"
    /** @var array<StatementInterface> $list */
    public array $list;
    public int $rbrace; // position of "}"

    public function __construct()
    {
        $this->lbrace = 0;
        $this->list = [];
        $this->rbrace = 0;
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
        return '';
    }

    public function stmtNode(): void
    {
        // TODO: Implement stmtNode() method.
    }
}
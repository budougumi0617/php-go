<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;

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

    /**
     * BlockStmt constructor.
     * @param int $lbrace {の位置。
     * @param array<StatementInterface> $list bodyの中にある文のリスト。
     * @param int $rbrace }の位置。
     */
    public function __construct(int $lbrace, array $list, int $rbrace)
    {
        $this->lbrace = $lbrace;
        $this->list = $list;
        $this->rbrace = $rbrace;
    }

    public static function castBlockStmt(NodeInterface $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of BlockStmt");
        }
        return $obj;
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
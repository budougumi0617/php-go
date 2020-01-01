<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Ast\NodeInterface;

interface StatementInterface extends NodeInterface
{
    public function stmtNode(): void;
}

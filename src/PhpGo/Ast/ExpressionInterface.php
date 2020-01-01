<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Ast\NodeInterface;

interface ExpressionInterface extends NodeInterface
{
    public function exprNode(): void;
}

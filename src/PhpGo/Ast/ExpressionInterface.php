<?php declare(strict_types=1);

namespace PhpGo\Ast;

interface ExpressionInterface extends NodeInterface
{
    public function exprNode(): void;
}

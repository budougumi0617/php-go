<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class ExprStmt
 *
 * An ExprStmt node represents a (stand-alone) expression
 * in a statement list.
 *
 * @package PhpGo\Ast
 * port from go/ast/ExprStmt
 */
final class ExprStmt implements StatementInterface
{
    public ExpressionInterface $x;

    public function __construct(ExpressionInterface $x)
    {
        $this->x = $x;
    }

    public function tokenLiteral(): string
    {
        return $this->x->tokenLiteral();
    }

    public function stmtNode(): void
    {
        // TODO: Implement stmtNode() method.
    }
}
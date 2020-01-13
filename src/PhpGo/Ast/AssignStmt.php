<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Token\Token;

/**
 * Class AssignStmt
 * @package PhpGo\Ast
 *
 * port go/ast/AssignStmt
 */
final class AssignStmt implements StatementInterface
{
    /** @var array<ExpressionInterface> $lhs */
    public array $lhs;
    // TokPos token.Pos   // position of Tok
    public Token $tok; // assignment token, DEFINE
    /** @var array<ExpressionInterface> $rhs */
    public array $rhs;

    public function tokenLiteral(): string
    {
        $lhs = '';
        foreach ($this->lhs as $l) {
            $lhs .= $l->token->tokenLiteral();
        }
        $rhs = '';
        foreach ($this->rhs as $r) {
            $rhs .= $r->token->tokenLiteral();
        }
        $op = $this->tok->string();
        return $lhs . $op . $rhs;
    }

    public function stmtNode(): void
    {
        // TODO: Implement stmtNode() method.
    }
}
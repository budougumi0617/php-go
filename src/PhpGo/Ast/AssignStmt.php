<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;
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

    /**
     * AssignStmt constructor.
     * @param array<ExpressionInterface> $lhs
     * @param Token $tok
     * @param array<ExpressionInterface> $rhs
     */
    public function __construct(array $lhs, Token $tok, array $rhs)
    {
        $this->lhs = $lhs;
        $this->tok = $tok;
        $this->rhs = $rhs;
    }

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

    public static function castAssignStmt(NodeInterface $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of BinaryExpr");
        }
        return $obj;
    }
}
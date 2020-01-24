<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;

/**
 * Class CallExpr
 * @package PhpGo\Ast
 *
 * port from go/ast/CallExpr
 */
final class CallExpr implements ExpressionInterface
{

    public ExpressionInterface $fun; // function expression
    public int $lparen; // position of "("
    /** @var array<ExpressionInterface>|null $args */
    public array $args; // function arguments; or nil
    public int $ellipsis; // position of "..." (token.NoPos if there is no "...")
    public int $rparen; // position of ")"

    /**
     * CallExpr constructor.
     * @param ExpressionInterface $fun
     * @param int $lparen
     * @param array|null $args
     * @param int $ellipsis
     * @param int $rparen
     */
    public function __construct(ExpressionInterface $fun, int $lparen, ?array $args, int $ellipsis, int $rparen)
    {
        $this->fun = $fun;
        $this->lparen = $lparen;
        $this->args = $args;
        $this->ellipsis = $ellipsis;
        $this->rparen = $rparen;
    }

    public static function castCallExpr(NodeInterface $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of CallExpr");
        }
        return $obj;
    }

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public function tokenLiteral(): string
    {
        $convertExpr = fn($obj): ExpressionInterface => $obj;
        $convertString = fn($expr): string => $convertExpr($expr)->tokenLiteral();
        $argsStrings = array_map($convertString, $this->args);
        $args = implode(",", $argsStrings);
        return $this->fun->tokenLiteral() . "({$args})";
    }
}

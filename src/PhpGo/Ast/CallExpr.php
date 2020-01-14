<?php declare(strict_types=1);

namespace PhpGo\Ast;

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
     * @param array<ExpressionInterface>|null $args
     */
    public function __construct(ExpressionInterface $fun, ?array $args)
    {
        $this->fun = $fun;
        $this->lparen = 0;
        $this->args = $args;
        $this->ellipsis = 0;
        $this->rparen = 0;
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

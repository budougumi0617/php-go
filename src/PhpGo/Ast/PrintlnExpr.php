<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;

/**
 * Class PrintlnExpr
 *
 * 評価時にechoするためのクラス。
 *
 * @package PhpGo\Ast
 */
final class PrintlnExpr implements StatementInterface
{
    public string $argName;

    /**
     * PrintExpr constructor.
     *
     * @param string $argName Scope内にあることが期待されるechoしたい変数名。
     */
    public function __construct(string $argName)
    {
        $this->argName = $argName;
    }

    public static function castPrintlnExpr(NodeInterface $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of PrintlnExpr");
        }
        return $obj;
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }

    public function stmtNode(): void
    {
        // TODO: Implement stmtNode() method.
    }
}
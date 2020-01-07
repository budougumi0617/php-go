<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class Program
 * @package PhpGo\Ast
 *
 * TODO: Implement ArrayAccess for strict type declaration.
 * statementsの処理が雑すぎる…
 * https://www.php.net/manual/ja/class.arrayaccess.php
 *
 * TODO: real implementation is go/ast/Package type
 * 本当はFile構造体に相当する構造が必要そう
 * https://godoc.org/go/ast#File
 */
final class Program implements NodeInterface
{
    public Ident $name; // package name.
    public array $statements; // StatementInterface array.

    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }

    public function tokenLiteral(): string
    {
        if (count($this->statements) > 0 && $this->statements[0] instanceof StatementInterface) {
            $stmt = $this->statements[0];
            return $stmt->tokenLiteral();
        }
        return '';
    }
}

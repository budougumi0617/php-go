<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;
use PhpGo\Token\Token;

/**
 * Class BasicLit
 * @package PhpGo\Ast
 *
 * https://godoc.org/go/ast#BasicLit
 */
final class BasicLit implements ExpressionInterface
{
    public int $valuePos; // literal position
    public Token $kind; // token.INT, token.FLOAT, token.IMAG, token.CHAR, or token.STRING
    public string $value; // literal string; e.g. 42, 0x7f, 3.14, 1e-9, 2.4i, 'a', '\x7f', "foo" or `\m\n\o`

    public function __construct(Token $kind)
    {
        $this->valuePos = 0;
        $this->kind = $kind;
        $this->value = $kind->literal;
    }

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public function tokenLiteral(): string
    {
        return $this->kind->string() . ', value: ' . $this->value;
    }


    public static function castBasicLit(NodeInterface $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of BasicLit");
        }
        return $obj;
    }
}
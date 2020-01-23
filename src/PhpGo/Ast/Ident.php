<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;

/**
 * Class Ident
 * @package PhpGo\Ast
 *
 * https://godoc.org/go/ast#Ident
 */
final class Ident implements ExpressionInterface
{
    public int $namePos; // identifier position
    public string $name; // identifier name
    public ?GoObject $object; // denoted object; or nil

    public function __construct(string $name)
    {
        $this->object = null;
        $this->name = $name;
        // FIXME: need to keep position.
        $this->namePos = 0;
    }

    public function tokenLiteral(): string
    {
        return $this->name;
    }

    public function exprNode(): void
    {
        // TODO: Implement exprNode() method.
    }

    public static function castIdent(NodeInterface $obj): Ident
    {
        if (!($obj instanceof Ident)) {
            throw new InvalidArgumentException("{$obj} is not instance of Ident");
        }
        return $obj;
    }
}

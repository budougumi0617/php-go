<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class Ident
 * @package PhpGo\Ast
 *
 * https://godoc.org/go/ast#Ident
 */
final class Ident
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
}

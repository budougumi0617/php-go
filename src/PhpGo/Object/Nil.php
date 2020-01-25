<?php declare(strict_types=1);

namespace PhpGo\Object;

use InvalidArgumentException;

final class Nil implements GoObject
{
    public function type(): ObjectType
    {
        return ObjectType::NIL_OBJ();
    }

    public function inspect(): string
    {
        return 'nil';
    }

    public static function castNil(GoObject $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of Nil");
        }
        return $obj;
    }
}

<?php declare(strict_types=1);

namespace PhpGo\Object;

use InvalidArgumentException;

final class ReturnObj implements GoObject
{
    public GoObject $value;

    public function __construct(GoObject $value)
    {
        $this->value = $value;
    }

    public function type(): ObjectType
    {
        return ObjectType::INTEGER_OBJ();
    }

    public function inspect(): string
    {
        return $this->value->inspect();
    }

    public static function castReturnObj(GoObject $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of ReturnObj");
        }
        return $obj;
    }
}

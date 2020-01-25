<?php declare(strict_types=1);

namespace PhpGo\Object;

use InvalidArgumentException;

final class Integer implements GoObject
{
    public int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function type(): ObjectType
    {
        return ObjectType::INTEGER_OBJ();
    }

    public function inspect(): string
    {
        return strval($this->value);
    }

    public static function castInteger(GoObject $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of Integer");
        }
        return $obj;
    }
}

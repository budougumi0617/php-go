<?php declare(strict_types=1);

namespace PhpGo\Object;

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
}

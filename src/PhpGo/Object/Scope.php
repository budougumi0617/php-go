<?php declare(strict_types=1);

namespace PhpGo\Object;

final class Scope
{
    /** @var array<string,GoObject> */
    private array $store;

    public function __construct()
    {
        $this->store = [];
    }

    public function get(string $name): GoObject
    {
        if (!array_key_exists($name, $this->store)) {
            throw new \UnexpectedValueException("{$name} is not exist");
        }
        return $this->store[$name];
    }

    public function set(string $name, GoObject $obj): GoObject
    {
        $this->store[$name] = $obj;
        return $obj;
    }
}
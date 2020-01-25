<?php declare(strict_types=1);

namespace PhpGo\Object;

use UnexpectedValueException;

final class Scope
{
    /** @var array<string,GoObject> */
    private array $store;
    private self $outer;

    public function __construct(Scope $outer = null)
    {
        $this->store = [];
        $this->outer = $outer;
    }

    public function get(string $name): GoObject
    {
        $result = null;
        if (array_key_exists($name, $this->store)) {
            $result = $this[$name];
        } else {    
            $result = $this->outer->get($name);
        }
        if (is_null($result)) {
            throw new UnexpectedValueException("{$name} is not exist");
        }
        return $result;
    }

    public function set(string $name, GoObject $obj): GoObject
    {
        $this->store[$name] = $obj;
        return $obj;
    }
}
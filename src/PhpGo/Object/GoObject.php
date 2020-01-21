<?php declare(strict_types=1);

namespace PhpGo\Object;

interface GoObject
{
    public function type(): ObjectType;
    public function inspect(): string;
}
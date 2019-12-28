<?php declare(strict_types=1);

namespace PhpGo\Token;

class Token
{
    public string $type;
    public string $literal;

    public function __construct(string $type, string $literal)
    {
        $this->type = $type;
        $this->literal = $literal;
    }
}

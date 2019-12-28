<?php declare(strict_types=1);

namespace PhpGo\Token;

class Token
{
    public TokenType $type;
    public string $literal;

    public function __construct(TokenType $type, string $literal)
    {
        $this->type = $type;
        $this->literal = $literal;
    }
}

<?php declare(strict_types=1);

namespace PhpGo\Lexer;

use PhpGo\Token\IllegalType;
use PhpGo\Token\Token;

class Lexer
{
    private string $codes;

    public function __construct(string $codes)
    {
        $this->codes = $codes;
    }

    public function nextToken():Token
    {
        return new Token(null,"");
    }
}

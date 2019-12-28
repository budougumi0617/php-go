<?php declare(strict_types=1);

namespace PhpGo\Token;

final class BreakType extends TokenType
{
    public function __construct()
    {
        $this->type = self::T_BREAK;
    }
}

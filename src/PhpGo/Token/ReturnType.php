<?php declare(strict_types=1);

namespace PhpGo\Token;

final class ReturnType extends TokenType
{
    public function __construct()
    {
        $this->type = self::T_RETURN;
    }
}

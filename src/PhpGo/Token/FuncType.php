<?php declare(strict_types=1);

namespace PhpGo\Token;

final class FuncType extends TokenType
{
    public function __construct()
    {
        $this->type = self::T_FUNC;
    }
}

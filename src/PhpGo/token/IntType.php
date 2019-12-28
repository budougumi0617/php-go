<?php declare(strict_types=1);


namespace PhpGo\Token;


final class IntType extends TokenType
{
    public function __construct()
    {
        $this->type = self::T_INT;
    }
}
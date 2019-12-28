<?php declare(strict_types=1);


namespace PhpGo\Token;


final class AddType extends TokenType
{
    public function __construct()
    {
        $this->type = self::T_ADD;
    }
}
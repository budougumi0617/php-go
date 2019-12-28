<?php declare(strict_types=1);


namespace PhpGo\token;


final class IndentTypes extends TokenType
{
    public function __construct()
    {
        $this->type = self::T_IDENT;
    }
}
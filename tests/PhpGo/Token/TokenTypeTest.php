<?php

namespace Tests\PhpGo\Token;

use PhpGo\Token\IllegalType;
use PhpGo\Token\TokenType;
use PHPUnit\Framework\TestCase;

class TokenTypeTest extends TestCase
{
    public function test_getType(): void
    {
        $got = new IllegalType();
        $this->assertSame(TokenType::T_ILLEGAL, $got->getType());
    }
}

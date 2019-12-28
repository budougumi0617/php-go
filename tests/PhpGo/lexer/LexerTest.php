<?php declare(strict_types=1);

namespace Tests\PhpGo;

use PhpGo\Lexer\Lexer;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testLoad(): void
    {
        $lexer = new Lexer();
        self::assertNotNull($lexer, "success load");
    }
}

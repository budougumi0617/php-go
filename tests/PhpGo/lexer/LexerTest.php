<?php declare(strict_types=1);

namespace Tests\PhpGo\Lexer;

use PhpGo\Lexer\Lexer;
use PhpGo\Token\AddType;
use PhpGo\Token\AssignType;
use PhpGo\Token\CommaType;
use PhpGo\Token\EofType;
use PhpGo\Token\LbraceType;
use PhpGo\Token\LparenType;
use PhpGo\Token\RbraceType;
use PhpGo\Token\RparenType;
use PhpGo\Token\SemicolonType;
use PhpGo\Token\Token;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    public function test_nextToken(): void
    {
        $input = "=+(){},;";

        $expectedTokens = [
            new Token(new AssignType(), "="),
            new Token(new AddType(), "+"),
            new Token(new LparenType(), "("),
            new Token(new RparenType(), ")"),
            new Token(new LbraceType(), "{"),
            new Token(new RbraceType(), "}"),
            new Token(new CommaType(), ","),
            new Token(new SemicolonType(), ";"),
            new Token(new EofType(), ""),
        ];

        $lexer = new Lexer($input);
        foreach ($expectedTokens as $expectedToken) {
            $token = $lexer->nextToken();
            self::assertEquals($expectedToken->type, $token->type);
            self::assertEquals($expectedToken->literal, $token->literal);
        }
    }
}

<?php declare(strict_types=1);

namespace Tests\PhpGo;

use PhpGo\Lexer\Lexer;
use PhpGo\Token\Token;
use PhpGo\Token\TokenType;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    public function test_nextToken(): void{
        $input= "=+(){},;";

        $expectedTokens = [
            new Token(TokenType::T_ASSIGN, "="),
            new Token(TokenType::T_ADD, "+"),
            new Token(TokenType::T_LPAREN, "("),
            new Token(TokenType::T_RPAREN, ")"),
            new Token(TokenType::T_LBRACE, "{"),
            new Token(TokenType::T_RBRACE, "}"),
            new Token(TokenType::T_COMMA, ","),
            new Token(TokenType::T_SEMICOLON, ";"),
            new Token(TokenType::T_EOF, ""),
        ];

        $lexer = new Lexer($input);
        foreach ($expectedTokens as $expectedToken){
            $token = $lexer->nextToken();
            self::assertSame($expectedToken->type, $token->type);
            self::assertSame($expectedToken->literal, $token->literal);
        }
}
}

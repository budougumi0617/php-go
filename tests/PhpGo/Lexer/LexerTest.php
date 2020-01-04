<?php declare(strict_types=1);

namespace Tests\PhpGo\Lexer;

use PhpGo\Lexer\Lexer;
use PhpGo\Token\AddType;
use PhpGo\Token\AssignType;
use PhpGo\Token\CommaType;
use PhpGo\Token\DefineType;
use PhpGo\Token\EofType;
use PhpGo\Token\FuncType;
use PhpGo\Token\IdentType;
use PhpGo\Token\IntType;
use PhpGo\Token\LbraceType;
use PhpGo\Token\LparenType;
use PhpGo\Token\PackageType;
use PhpGo\Token\RbraceType;
use PhpGo\Token\ReturnType;
use PhpGo\Token\RparenType;
use PhpGo\Token\SemicolonType;
use PhpGo\Token\StringType;
use PhpGo\Token\Token;
use PhpGo\Token\VarType;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    /**
     * @dataProvider providerForNextToken
     *
     * @param string $input
     * @param array $expectedTokens
     */
    public function test_nextToken(string $input, array $expectedTokens): void
    {
        $lexer = new Lexer($input);
        foreach ($expectedTokens as $expectedToken) {
            $token = $lexer->nextToken();
            self::assertEquals($expectedToken->type, $token->type, "expect {$expectedToken->type->getType()}, bad {$token->type->getType()}");
            self::assertEquals($expectedToken->literal, $token->literal, "{$expectedToken->type->getType()} failed");
        }
    }

    public function providerForNextToken()
    {
        // parse result https://play.golang.org/p/nvk5BXnBHZd
        $complexInput = <<<EOT
package main

var five = 5
var ten = 10

func add(x, y int) int {
	return x + y
}

func main(){
	result := add(five, ten)
	print(result)
}
EOT;
        return [
            'simple' => ['=+(){},;', [
                new Token(new AssignType(), ""),
                new Token(new AddType(), ""),
                new Token(new LparenType(), ""),
                new Token(new RparenType(), ""),
                new Token(new LbraceType(), ""),
                new Token(new RbraceType(), ""),
                new Token(new CommaType(), ""),
                new Token(new SemicolonType(), ";"),
                new Token(new EofType(), ""),
            ]],
            'complex' => [$complexInput, [
                new Token(new PackageType(), "package"),
                new Token(new IdentType(), "main"),
                new Token(new SemicolonType(), "\n"),
                new Token(new VarType(), "var"),
                new Token(new IdentType(), "five"),
                new Token(new AssignType(), ""),
                new Token(new IntType(), "5"),
                new Token(new SemicolonType(), "\n"),
                new Token(new VarType(), "var"),
                new Token(new IdentType(), "ten"),
                new Token(new AssignType(), ""),
                new Token(new IntType(), "10"),
                new Token(new SemicolonType(), "\n"),
                new Token(new FuncType(), "func"),
                new Token(new IdentType(), "add"),
                new Token(new LparenType(), ""),
                new Token(new IdentType(), "x"),
                new Token(new CommaType(), ""),
                new Token(new IdentType(), "y"),
                new Token(new IdentType(), "int"),
                new Token(new RparenType(), ""),
                new Token(new IdentType(), "int"),
                new Token(new LbraceType(), ""),
                new Token(new ReturnType(), "return"),
                new Token(new IdentType(), "x"),
                new Token(new AddType(), ""),
                new Token(new IdentType(), "y"),
                new Token(new SemicolonType(), "\n"),
                new Token(new RbraceType(), ""),
                new Token(new SemicolonType(), "\n"),
                new Token(new FuncType(), "func"),
                new Token(new IdentType(), "main"),
                new Token(new LparenType(), ""),
                new Token(new RparenType(), ""),
                new Token(new LbraceType(), ""),
                new Token(new IdentType(), "result"),
                new Token(new DefineType(), ""),
                new Token(new IdentType(), "add"),
                new Token(new LparenType(), ""),
                new Token(new IdentType(), "five"),
                new Token(new CommaType(), ""),
                new Token(new IdentType(), "ten"),
                new Token(new RparenType(), ""),
                new Token(new SemicolonType(), "\n"),
                new Token(new IdentType(), "print"),
                new Token(new LparenType(), ""),
                new Token(new IdentType(), "result"),
                new Token(new RparenType(), ""),
                new Token(new SemicolonType(), "\n"),
                new Token(new RbraceType(), ""),
                new Token(new SemicolonType(), "\n"),
                new Token(new EofType(), ""),
            ]],
            // https://play.golang.org/p/5WkX9KibpZf
            'string' => ['=+()"string_literal"{},;', [
                new Token(new AssignType(), ""),
                new Token(new AddType(), ""),
                new Token(new LparenType(), ""),
                new Token(new RparenType(), ""),
                new Token(new StringType(), "\"string_literal\""),
                new Token(new LbraceType(), ""),
                new Token(new RbraceType(), ""),
                new Token(new CommaType(), ""),
                new Token(new SemicolonType(), ";"),
                new Token(new EofType(), ""),
            ]],
        ];
    }
}

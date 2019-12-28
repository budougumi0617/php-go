<?php declare(strict_types=1);

namespace PhpGo\Lexer;

use PhpGo\Token\AddType;
use PhpGo\Token\AssignType;
use PhpGo\Token\CommaType;
use PhpGo\Token\EofType;
use PhpGo\Token\IllegalType;
use PhpGo\Token\LbraceType;
use PhpGo\Token\LparenType;
use PhpGo\Token\RbraceType;
use PhpGo\Token\RparenType;
use PhpGo\Token\SemicolonType;
use PhpGo\Token\Token;

class Lexer
{
    private static string $null = "\0";
    private string $codes;
    private int $position; // last read position.
    private int $readPosition; // next read position.
    private string $ch; // current traversing character.

    private string $NUL; //  the null-terminated character.

    public function __construct(string $codes)
    {
        $this->codes = $codes;
        $this->position = 0;
        $this->readPosition = 0;
        $this->readCharacter();
    }

    private function readCharacter(): void
    {
        if ($this->readPosition >= strlen($this->codes)) {
            $this->ch = $this::$null;
        } else {
            $this->ch = substr($this->codes, $this->readPosition, 1);
        }
        $this->position = $this->readPosition;
        $this->readPosition++;
    }

    public function nextToken(): Token
    {
        $token = null;
        switch ($this->ch) {
            case "=":
                $token = new Token(new AssignType(), $this->ch);
                break;
            case ";":
                $token = new Token(new SemicolonType(), $this->ch);
                break;
            case "(":
                $token = new Token(new LparenType(), $this->ch);
                break;
            case ")":
                $token = new Token(new RparenType(), $this->ch);
                break;
            case ",":
                $token = new Token(new CommaType(), $this->ch);
                break;
            case "+":
                $token = new Token(new AddType(), $this->ch);
                break;
            case "{":
                $token = new Token(new LbraceType(), $this->ch);
                break;
            case "}":
                $token = new Token(new RbraceType(), $this->ch);
                break;
            case $this::$null:
                $token = new Token(new EofType(), "");
                break;
            default:
                $token = new Token(new IllegalType(), $this->ch);
        }

        $this->readCharacter();
        return $token;
    }
}

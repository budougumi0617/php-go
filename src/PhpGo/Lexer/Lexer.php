<?php declare(strict_types=1);

namespace PhpGo\Lexer;

use PhpGo\Token\AddType;
use PhpGo\Token\AssignType;
use PhpGo\Token\CommaType;
use PhpGo\Token\EofType;
use PhpGo\Token\IdentType;
use PhpGo\Token\IllegalType;
use PhpGo\Token\LbraceType;
use PhpGo\Token\LparenType;
use PhpGo\Token\RbraceType;
use PhpGo\Token\RparenType;
use PhpGo\Token\SemicolonType;
use PhpGo\Token\Token;
use function PhpGo\Token\lookupIndent;

class Lexer
{
    private static string $null = "\0";
    private string $codes;
    private int $position; // last read position.
    private int $readPosition; // next read position.
    private string $ch; // current traversing character.
    private bool $insertSemi; // insert a semicolon before next newline

    private string $NUL; //  the null-terminated character.

    public function __construct(string $codes)
    {
        $this->codes = $codes;
        $this->position = 0;
        $this->readPosition = 0;
        $this->insertSemi = false;
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

    private function isLetter(string $ch): bool
    {
        // FIXME: un-support UTF-8
        return 'a' <= strtolower($ch) && strtolower($ch) <= 'z' || $ch == '_';
        // || $ch >= utf8.RuneSelf && $unicode.IsLetter(ch)
    }

    private function readIdentifier(): string
    {
        $pos = $this->position;
        while ($this->isLetter($this->ch)) {
            $this->readCharacter();
        }
        return substr($this->codes, $pos, $this->position - $pos); // [$pos:$this->position]
    }

    /**
     * "whitespace" definition is decided by the language specification.
     */
    private function skipWhitespace(): void
    {
        while ($this->ch == ' ' || $this->ch == "\t" || $this->ch == "\n" && !$this->insertSemi || $this->ch == "\r") {
            $this->readCharacter();
        }
    }

    public function nextToken(): Token
    {
        $this->skipWhitespace();

        $token = null;
        $insertSemi = false;
        switch ($this->ch) {
            case "\n":
                // we only reach here if s.insertSemi was
                // set in the first place and exited early
                // from s.skipWhitespace()
                $this->insertSemi = false; // newline consumed
                return new Token(new SemicolonType(), "\n");
            case "=":
                $token = new Token(new AssignType(), "");
                break;
            case ";":
                $token = new Token(new SemicolonType(), $this->ch);
                break;
            case "(":
                $token = new Token(new LparenType(), "");
                break;
            case ")":
                $insertSemi = true;
                $token = new Token(new RparenType(), "");
                break;
            case ",":
                $token = new Token(new CommaType(), "");
                break;
            case "+":
                // TODO: switch +, +=, or ++.
                $token = new Token(new AddType(), $this->ch);
                break;
            case "{":
                $token = new Token(new LbraceType(), "");
                break;
            case "}":
                $insertSemi = true;
                $token = new Token(new RbraceType(), "");
                break;
            case $this::$null:
                $token = new Token(new EofType(), "");
                break;
            default:
                if ($this->isLetter($this->ch)) {
                    $literal = $this->readIdentifier();
                    $type = lookupIndent($literal);
                    $this->insertSemi = true;
                    // not need to call readCharacter.
                    return new Token($type, $literal);
                } else {
                    $insertSemi = $this->insertSemi;
                    $token = new Token(new IllegalType(), $this->ch);
                }
        }

        $this->insertSemi = $insertSemi;
        $this->readCharacter();
        return $token;
    }
}

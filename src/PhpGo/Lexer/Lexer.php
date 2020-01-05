<?php declare(strict_types=1);

namespace PhpGo\Lexer;

use PhpGo\Token\AddType;
use PhpGo\Token\AssignType;
use PhpGo\Token\ColonType;
use PhpGo\Token\CommaType;
use PhpGo\Token\DefineType;
use PhpGo\Token\EofType;
use PhpGo\Token\IllegalType;
use PhpGo\Token\IntType;
use PhpGo\Token\LbraceType;
use PhpGo\Token\LparenType;
use PhpGo\Token\RbraceType;
use PhpGo\Token\RparenType;
use PhpGo\Token\SemicolonType;
use PhpGo\Token\StringType;
use PhpGo\Token\Token;

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

    /**
     * TODO: real implementation.
     * go/scanner/Scanner.next.
     */
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

    /**
     * go/token/token.isDecimal
     * @param string $ch
     * @return bool
     */
    private function isDecimal(string $ch): bool
    {
        return "0" <= $ch && $ch <= "9";
    }

    /**
     * FIXME: support only Integer.
     * go/token/token.scanNumber
     * @return Token
     */
    private function readNumber(): Token
    {
        $type = new IntType();
        $pos = $this->position;
        while ($this->isDecimal($this->ch)) {
            $this->readCharacter();
        }
        $literal = substr($this->codes, $pos, $this->position - $pos);
        return new Token($type, $literal);
    }

    /**
     * Returns the byte following the most recently read character without advancing the scanner.
     *
     * go/scanner/Scanner.peek
     *
     * @return string next character or this::null if reached EOF.
     */
    private function peek(): string
    {
        if ($this->readPosition >= strlen($this->codes)) {
            return $this::$null;
        }
        return $this->codes[$this->readPosition];
    }

    /**
     * @return string
     *
     * https://github.com/golang/go/blob/8adc1e00aa1a92a85b9d6f3526419d49dd7859dd/src/go/scanner/scanner.go#L636
     */
    private function scanString(): string
    {
        // TODO: Scannerとはロジックが若干違うので、while前に一文字すすめる。
        // $offs = $this->position - 1; // '"' opening already consumed
        $offs = $this->position; // '"' opening already consumed
        $this->readCharacter();
        while (true) {
            $ch = $this->ch;
            if ($ch == "\n" || $ch == self::$null) {
                throw new \UnexpectedValueException("string literal not terminated");
            }
            $this->readCharacter();
            if ($ch == '"') {
                break;
            }
            if ($ch == "\\") {
                $this->scanEscape('"');
            }
        }
        return substr($this->codes, $offs, $this->position - $offs); // [$offs:$this->position]
    }

    /**
     * @param string $quote a character.
     * @return bool false if the offending character (without consuming it). Otherwise return true.
     *
     * scanEscape is ported from go/scanner/Scanner.scanEscape.
     *
     * scanEscape parses an escape sequence where rune is the accepted
     * escaped quote. In case of a syntax error, it stops at the offending
     * character (without consuming it) and returns false. Otherwise
     * it returns true.
     */
    private function scanEscape(string $quote): bool
    {
        $offs = $this->position;

        $n = 0;
        $base = 0;
        $max = 0;
        switch ($this->ch) {
            case 'a':
            case 'b':
            case 'f':
            case 'n':
            case 'r':
            case 't':
            case 'v':
            case '\\':
            case $quote:
                $this->readCharacter();
                return true;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
                $n = 3;
                $base = 8;
                $max = 255;
                break;
            case 'x':
                $this->readCharacter();
                $n = 2;
                $base = 16;
                $max = 255;
                break;
            case 'u':
                $this->readCharacter();
                $n = 4;
                $base = 16;
                $max = -1; // FIXME: unicode.MaxRune
                break;
            case 'U':
                $this->readCharacter();
                $n = 8;
                $base = 16;
                $max = -1; // FIXME: unicode.MaxRune
                break;
            default:
                if ($this->ch == self::$null) {
                    throw new \UnexpectedValueException("escape sequence not terminated");
                }
                throw new \UnexpectedValueException("unknown escape sequence");
        }
        $x = 0;
        // TODO: port from Go to PHP.
        // for n > 0 {
        //    d := uint32(digitVal(s.ch))
        //    if d >= base {
        //        msg := fmt.Sprintf("illegal character %#U in escape sequence", s.ch)
        //        if s.ch < 0 {
        //            msg = "escape sequence not terminated"
        //        }
        //        s.error(s.offset, msg)
        //        return false
        //    }
        //    x = x*base + d
        //    s.next()
        //    n--
        // }
        if ($x > $max || 0xD800 <= $x && $x < 0xE000) {
            // s.error(offs, "escape sequence is invalid Unicode code point")
            throw new \InvalidArgumentException("escape sequence is invalid Unicode code point");
        }
        // TODO: 現状の実装では\"しか観ていないはず。
        throw new \LogicException("not through end function");
        // return true;
    }

    /**
     * go/token/Token.switch2
     *
     * @param Token $tok0
     * @param Token $tok1
     * @return Token
     */
    private function switch2(Token $tok0, Token $tok1): Token
    {
        if ($this->peek() == "=") {
            $this->readCharacter();
            return $tok1;
        }
        return $tok0;
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
            case '"':
                $this->insertSemi = true;
                return new Token(new StringType(), $this->scanString());
                break;
            case "=":
                $token = new Token(new AssignType(), "");
                break;
            case ":":
                $tok0 = new Token(new ColonType(), "");
                $tok1 = new Token(new DefineType(), "");
                $token = $this->switch2($tok0, $tok1);
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
                $token = new Token(new AddType(), "");
                break;
            case "{":
                $token = new Token(new LbraceType(), "");
                break;
            case "}":
                $insertSemi = true;
                $token = new Token(new RbraceType(), "");
                break;
            case $this::$null:
                if ($this->insertSemi) {
                    $this->insertSemi = false; // EOF consumed
                    return new Token(new SemicolonType(), "\n");
                }
                $token = new Token(new EofType(), "");
                break;
            default:
                if ($this->isLetter($this->ch)) {
                    $literal = $this->readIdentifier();
                    $type = Token::lookupIndent($literal);
                    $this->insertSemi = true;
                    // not need to call readCharacter.
                    return new Token($type, $literal);
                } elseif ($this->isDecimal($this->ch)) {
                    // FIXME: 本当の条件は isDecimal(ch) || ch == '.' && isDecimal(rune(s.peek()))
                    $this->insertSemi = true;
                    // not need to call readCharacter(maybe...).
                    return $this->readNumber();
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

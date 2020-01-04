<?php declare(strict_types=1);

namespace PhpGo\Parser;

use PhpGo\Ast\Program;
use PhpGo\Lexer\Lexer;
use PhpGo\Token\Token;

/**
 * Class Parser
 * @package PhpGo\Parser
 *
 * TODO: Real implementation
 * https://github.com/golang/go/blob/master/src/go/parser/parser.go
 */
final class Parser
{
    private Lexer $lexer;
    private ?Token $curToken;
    private ?Token $peekToken;

    public function __construct(Lexer $l)
    {
        $this->lexer = $l;
        $this->peekToken = null;
        $this->curToken= null;
        // initialize $curToken, $peekToken.
        $this->nextToken();
        $this->nextToken();
    }

    public function nextToken(): void
    {
        $this->curToken = $this->peekToken;
        $this->peekToken = $this->lexer->nextToken();
    }

    public function parseProgram(): Program
    {
        return new Program([]);
    }
}

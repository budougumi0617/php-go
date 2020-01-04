<?php declare(strict_types=1);

namespace Repl;

use PhpGo\Lexer\Lexer;
use PhpGo\Token\EofType;
use PhpGo\Token\SemicolonType;

const PROMPT = '>> ';

function Start(): void
{
    while (true) {
        echo PROMPT;
        $line = trim(fgets(STDIN));
        $lexer = new Lexer($line);

        while (true) {
            $tok = $lexer->nextToken();
            echo $tok->string() . PHP_EOL;
            if ($tok->type() instanceof EofType) {
                break;
            }
        }
    }
}

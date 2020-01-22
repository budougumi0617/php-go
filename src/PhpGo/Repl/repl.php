<?php declare(strict_types=1);

namespace Repl;

use PhpGo\Evaluator\Evaluator;
use PhpGo\Lexer\Lexer;
use PhpGo\Parser\Parser;
use PhpGo\Token\EofType;
use PhpGo\Token\SemicolonType;

const PROMPT = '>> ';

function Start(): void
{
    while (true) {
        echo PROMPT;
        $line = trim(fgets(STDIN));
        $lexer = new Lexer($line);

        // TODO: P128を見てEvalまで書く。
        $parser = new Parser($lexer);
        $program = $parser->parseProgram();
        // TODO: エラー処理を書くこと

        $evaluate = Evaluator::Eval($program);
        if (!is_null($evaluate)) {
            echo $evaluate->inspect() . PHP_EOL;
        }
    }
}

<?php declare(strict_types=1);

namespace PhpGo;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Token/Token.php';
require_once __DIR__ . '/Repl/repl.php';

use PhpGo\Lexer\Lexer;
use PhpGo\Token\EofType;
use PhpGo\Token\SemicolonType;
use function Repl\Start;

if (count($argv) > 1) {
    $input = $argv[1];
    $lexer = new Lexer($input);
    while (true) {
        $tok = $lexer->nextToken();
        echo $tok->string() . PHP_EOL;
        if ($tok->type instanceof EofType) {
            break;
        }
    }
} else {
    $user = get_current_user();
    echo "Hello {$user}! This is the Go language!" . PHP_EOL;
    echo "Feel free to type in commands" . PHP_EOL;

    Start();
}

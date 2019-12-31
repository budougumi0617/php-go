<?php declare(strict_types=1);

namespace PhpGo;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/Token/Token.php';

use PhpGo\Lexer\Lexer;
use PhpGo\Token\EofType;

$input = $argv[1];
$lexer = new Lexer($input);

while (true) {
    $tok = $lexer->nextToken();
    echo $tok->string() . PHP_EOL;
    if ($tok->type() instanceof EofType) {
        break;
    }
}

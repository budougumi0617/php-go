<?php declare(strict_types=1);

namespace PhpGo;

require_once "vendor/autoload.php";

use PhpGo\Lexer\Lexer;
use PhpGo\Token\EofType;

$input = 'var foo = 5';
$lexer = new Lexer($input);

for ($tok = $lexer->nextToken(); $tok->type() == EofType::class; $tok = $lexer->nextToken()) {
    var_dump($tok);
}

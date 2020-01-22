<?php declare(strict_types=1);

namespace PhpGo\Evaluator;

use PhpGo\Ast\Program;
use PhpGo\Object\GoObject;
use PhpGo\Object\Integer;

final class Evaluator
{
    public static function Eval(Program $program): GoObject
    {
        return new Integer(1);
    }
}

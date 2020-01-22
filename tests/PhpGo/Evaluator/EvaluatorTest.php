<?php declare(strict_types=1);

namespace Tests\PhpGo\Evaluator;

use PhpGo\Evaluator\Evaluator;
use PhpGo\Lexer\Lexer;
use PhpGo\Object\GoObject;
use PhpGo\Object\Integer;
use PhpGo\Parser\Parser;
use PHPUnit\Framework\TestCase;

final class EvaluatorTest extends TestCase
{

    /**
     * @dataProvider providerForEvalIntegerExpression
     * @param string $input
     * @param int $want
     */
    public function test_eval_integer_expression(string $input, int $want): void
    {
        $got = $this->testEval($input);
        $integer = Integer::castInteger($got);
        $this->assertEquals($want, $integer->value);
    }

    public function providerForEvalIntegerExpression():array {
        return [
            'five' => ['5', 5],
            'ten' => ['10', 10],
        ];
    }

    private function testEval(string $input): GoObject
    {
        $parser =  new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        return Evaluator::Eval($program);
    }
}

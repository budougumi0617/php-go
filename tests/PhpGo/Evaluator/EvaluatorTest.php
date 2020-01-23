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
     * @dataProvider providerForEvalAssign
     *
     * @param string $input 評価する文字列
     * @param int    $want  期待する整数値
     */
    public function test_eval_integer_expression(string $input, int $want): void
    {
        $got = $this->testEval($input);
        $integer = Integer::castInteger($got);
        $this->assertSame($want, $integer->value);
    }

    public function providerForEvalIntegerExpression(): array
    {
        return [
            'five' => ['5', 5],
            'ten' => ['10', 10],
            'add1' => ['10 + 5', 15],
            'add multi' => ['10 + 5 + 5', 20],
        ];
    }

    public function providerForEvalAssign(): array
    {
        return [
            'simple_assign' => ['x :=5', 5],
            'multiple_assign_x' => ["x, y := 10, 20\nx", 10],
            'multiple_assign_y' => ["x, y := 10, 20\ny", 20],
        ];
    }

    private function testEval(string $input): GoObject
    {
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        return Evaluator::Eval($program);
    }
}

<?php declare(strict_types=1);

namespace Tests\PhpGo\Evaluator;

use PhpGo\Ast\BasicLit;
use PhpGo\Ast\BlockStmt;
use PhpGo\Ast\Ident;
use PhpGo\Ast\PrintlnExpr;
use PhpGo\Evaluator\Evaluator;
use PhpGo\Lexer\Lexer;
use PhpGo\Object\FunctionObject;
use PhpGo\Object\GoObject;
use PhpGo\Object\Integer;
use PhpGo\Object\Nil;
use PhpGo\Object\Scope;
use PhpGo\Parser\Parser;
use PhpGo\Token\StringType;
use PhpGo\Token\Token;
use PhpGo\Token\TokenType;
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
        $got = $this->executeEval($input);
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
            'simple_assign' => ['x := 5', 5],
            'multiple_assign_x' => ["x, y := 10, 20\nx", 10],
            'multiple_assign_y' => ["x, y := 10, 20\ny", 20],
        ];
    }


    /**
     * @dataProvider providerForEvalFunction
     *
     * @param string   $input 評価する文字列
     * @param GoObject $want  期待する文字列
     */
    public function test_eval_call_function(string $input, GoObject $want): void
    {
        $got = $this->executeEval($input);
        $nil = Nil::castNil($got);
        $this->assertSame($want->inspect(), $nil->inspect());
    }

    public function providerForEvalFunction(): array
    {
        return [
            'simple_call' => ['println(10)', new Nil()],
            'variable_call' => ["x, y := 10, 20\nprintln(x)", new Nil()],
        ];
    }

    private function executeEval(string $input): GoObject
    {
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $root = new Scope();
        // 組み込み関数としてprintln関数を準備しておく。
        Evaluator::presetPrint($root);
        return Evaluator::Eval($program, $root);
    }
}

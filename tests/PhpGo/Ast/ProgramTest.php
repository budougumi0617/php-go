<?php

namespace Tests\PhpGo\Ast;

use PhpGo\Ast\Program;
use PhpGo\Ast\StatementInterface;
use PHPUnit\Framework\TestCase;

class ProgramTest extends TestCase
{
    public function testTokenLiteral()
    {
        $stmt = new class implements StatementInterface {
            public function tokenLiteral(): string
            {
                return 'test';
            }

            public function stmtNode(): void
            {
                // TODO: Implement stmtNode() method.
            }
        };
        $program = new Program([$stmt]);
        self::assertSame('test', $program->tokenLiteral());
    }
}

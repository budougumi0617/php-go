<?php declare(strict_types=1);

namespace Tests\PhpGo\Parser;

use PhpGo\Ast\CallExpr;
use PhpGo\Ast\ExprStmt;
use PhpGo\Ast\GenDecl;
use PhpGo\Ast\ImportSpec;
use PhpGo\Lexer\Lexer;
use PhpGo\Parser\Parser;
use PhpGo\Token\TokenType;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{

    public function test_parseProgram_GenDecl_ImportSpec(): void
    {
        // https://play.golang.org/p/RyvRgMFN79J
        $input = <<<EOT
import (
	"fmt"
	"log"
)
EOT;
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertEquals(1, count($program->statements));
        $this->assertTrue($program->statements[0] instanceof GenDecl);
        // cast GenDecl
        $convertFn = fn($obj): GenDecl => $obj;
        $genDecl = $convertFn($program->statements[0]);
        $this->assertEquals(TokenType::T_IMPORT, $genDecl->token->type->getType());

        $this->assertEquals(2, count($genDecl->specs));

        // cast ImportSpec
        $castImportSpec = fn($obj): ImportSpec => $obj;
        $is = $castImportSpec($genDecl->specs[0]);
        $this->assertEquals('"fmt"', $is->path->kind->literal);
        $is2 = $castImportSpec($genDecl->specs[1]);
        $this->assertEquals('"log"', $is2->path->kind->literal);
    }

    public function test_parseProgram_GenDecl_ImportSpec_single(): void
    {
        // https://play.golang.org/p/RyvRgMFN79J
        $input = <<<EOT
import "log"
EOT;
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertEquals(1, count($program->statements));
        $this->assertTrue($program->statements[0] instanceof GenDecl);
        // cast GenDecl
        $convertFn = fn($obj): GenDecl => $obj;
        $genDecl = $convertFn($program->statements[0]);
        $this->assertEquals(TokenType::T_IMPORT, $genDecl->token->type->getType());

        $this->assertEquals(1, count($genDecl->specs));

        // cast ImportSpec
        $castImportSpec = fn($obj): ImportSpec => $obj;
        $is = $castImportSpec($genDecl->specs[0]);
        $this->assertEquals('"log"', $is->path->kind->literal);
    }

    public function test_parseProgram_package(): void
    {
        // わざと改行とスペースを含んでいる。
        $input = <<<EOT

  
package main
EOT;
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertEquals('main', $program->name->name);
    }

//    // TODO: それなりに終わったらparseReturnStmtをprivateメソッドにするので消す。
//    public function test_parseReturnStmt(): void
//    {
//        $input = <<<EOT
//	return x + y
//EOT;
//        $parser = new Parser(new Lexer($input));
//        $program = $parser->parseProgram();
//        $this->assertNotNull($program);
//        $this->assertEquals('main', $program->name->name);
//    }

    public function test_parseProgram_easy_func(): void
    {
        $input = <<<EOT
package main
	
func main(){
	msg := "hello world"
}
EOT;
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertEquals('main', $program->name->name);
        $this->assertEquals(TokenType::T_FUNC, $program->statements[0]->type->getType());
        $this->assertEquals('msg', $program->statements[0]->body->list[0]->lhs[0]->name);
        $this->assertEquals('"hello world"', $program->statements[0]->body->list[0]->rhs[0]->value);
    }

    public function test_parseProgram_hello_world(): void
    {
        $input = <<<EOT
package main

func main(){
	msg := "hello world"
	print(msg)
}
EOT;
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertEquals('main', $program->name->name);
        $this->assertEquals(TokenType::T_FUNC, $program->statements[0]->type->getType());
        $this->assertEquals('msg', $program->statements[0]->body->list[0]->lhs[0]->name);
        $this->assertEquals('"hello world"', $program->statements[0]->body->list[0]->rhs[0]->value);
        $this->assertTrue($program->statements[0]->body->list[1] instanceof ExprStmt);
        $this->assertTrue($program->statements[0]->body->list[1]->x instanceof CallExpr);
        $this->assertEquals('print', $program->statements[0]->body->list[1]->x->fun->name);
        $this->assertEquals('msg', $program->statements[0]->body->list[1]->x->args[0]->name);
    }
}

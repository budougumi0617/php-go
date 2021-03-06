<?php declare(strict_types=1);

namespace Tests\PhpGo\Parser;

use PhpGo\Ast\AssignStmt;
use PhpGo\Ast\BinaryExpr;
use PhpGo\Ast\CallExpr;
use PhpGo\Ast\ExprStmt;
use PhpGo\Ast\GenDecl;
use PhpGo\Ast\Ident;
use PhpGo\Ast\ImportSpec;
use PhpGo\Lexer\Lexer;
use PhpGo\Parser\Parser;
use PhpGo\Token\AddType;
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
        $this->assertSame(1, count($program->statements));
        $this->assertTrue($program->statements[0] instanceof GenDecl);
        // cast GenDecl
        $convertFn = fn($obj): GenDecl => $obj;
        $genDecl = $convertFn($program->statements[0]);
        $this->assertSame(TokenType::T_IMPORT, $genDecl->token->type->getType());

        $this->assertSame(2, count($genDecl->specs));

        // cast ImportSpec
        $castImportSpec = fn($obj): ImportSpec => $obj;
        $is = $castImportSpec($genDecl->specs[0]);
        $this->assertSame('"fmt"', $is->path->kind->literal);
        $is2 = $castImportSpec($genDecl->specs[1]);
        $this->assertSame('"log"', $is2->path->kind->literal);
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
        $this->assertSame(1, count($program->statements));
        $this->assertTrue($program->statements[0] instanceof GenDecl);
        // cast GenDecl
        $convertFn = fn($obj): GenDecl => $obj;
        $genDecl = $convertFn($program->statements[0]);
        $this->assertSame(TokenType::T_IMPORT, $genDecl->token->type->getType());

        $this->assertSame(1, count($genDecl->specs));

        // cast ImportSpec
        $castImportSpec = fn($obj): ImportSpec => $obj;
        $is = $castImportSpec($genDecl->specs[0]);
        $this->assertSame('"log"', $is->path->kind->literal);
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
        $this->assertSame('main', $program->name->name);
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
//        $this->assertSame('main', $program->name->name);
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
        $this->assertSame('main', $program->name->name);
        $this->assertSame(TokenType::T_FUNC, $program->statements[0]->type->getType());
        $this->assertSame('msg', $program->statements[0]->body->list[0]->lhs[0]->name);
        $this->assertSame('"hello world"', $program->statements[0]->body->list[0]->rhs[0]->value);
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
        $this->assertSame('main', $program->name->name);
        $this->assertSame(TokenType::T_FUNC, $program->statements[0]->type->getType());
        $this->assertSame('msg', $program->statements[0]->body->list[0]->lhs[0]->name);
        $this->assertSame('"hello world"', $program->statements[0]->body->list[0]->rhs[0]->value);
        $this->assertTrue($program->statements[0]->body->list[1] instanceof ExprStmt);
        $this->assertTrue($program->statements[0]->body->list[1]->x instanceof CallExpr);
        $this->assertSame('print', $program->statements[0]->body->list[1]->x->fun->name);
        $this->assertSame('msg', $program->statements[0]->body->list[1]->x->args[0]->name);
    }

    public function test_parseProgram_integer(): void
    {
        $input = <<<EOT
10
EOT;
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertSame('10', $program->statements[0]->kind->literal);
    }

    public function test_parseProgram_BinaryExpr(): void
    {
        $input = <<<EOT
10+15
EOT;
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertTrue($program->statements[0] instanceof BinaryExpr);
        $this->assertSame('10', $program->statements[0]->x->kind->literal);
        $this->assertTrue($program->statements[0]->op->type instanceof AddType);
        $this->assertSame('15', $program->statements[0]->y->kind->literal);
    }

    public function test_parseProgram_AssignStmt(): void
    {
        $input = "x,y := 20, 30\nx";
        $parser = new Parser(new Lexer($input));
        $program = $parser->parseProgram();
        $this->assertNotNull($program);
        $this->assertTrue($program->statements[0] instanceof AssignStmt);
        $this->assertSame('x', $program->statements[0]->lhs[0]->name);
        $this->assertSame('y', $program->statements[0]->lhs[1]->name);
        $this->assertSame('20', $program->statements[0]->rhs[0]->value);
        $this->assertSame('30', $program->statements[0]->rhs[1]->value);
        $this->assertTrue($program->statements[1] instanceof Ident);
        $this->assertSame('x', $program->statements[1]->name);
    }
}

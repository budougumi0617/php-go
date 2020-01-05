<?php declare(strict_types=1);

namespace Tests\PhpGo\Parser;

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
}

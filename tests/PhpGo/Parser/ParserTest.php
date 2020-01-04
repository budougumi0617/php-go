<?php declare(strict_types=1);

namespace Tests\PhpGo\Parser;

use PhpGo\Ast\GenDecl;
use PhpGo\Ast\ImportSpec;
use PhpGo\Ast\SpecInterface;
use PhpGo\Lexer\Lexer;
use PhpGo\Parser\Parser;
use PhpGo\Token\TokenType;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
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
        $this->assertNull($program);
        $this->assertEquals(1, count($program->statements));
        $this->assertTrue($program->statements[0] instanceof GenDecl);
        // cast GenDecl
        $convertFn = fn($obj): GenDecl => $obj;
        $genDecl = $convertFn($program->statements[0]);
        $this->assertEquals(TokenType::T_IMPORT, $genDecl->token->type);

        $this->assertEquals(2, count($genDecl->specs));

        // cast ImportSpec
        $castImportSpec = fn($obj): ImportSpec => $obj;
        $is = $castImportSpec($genDecl->specs[0]);
        $this->assertEquals("fmt", $is->path);
        $is2 = $castImportSpec($genDecl->specs[1]);
        $this->assertEquals("log", $is2->path);
    }
}

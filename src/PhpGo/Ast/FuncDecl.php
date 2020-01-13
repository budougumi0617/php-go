<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Token\FuncType;

/**
 * Class FuncDecl
 * @package PhpGo\Ast
 *
 * port from go/ast/FuncDecl
 */
final class FuncDecl implements DeclarationInterface
{
    // Doc  *CommentGroup // associated documentation; or nil
    // Recv *FieldList    // receiver (methods); or nil (functions)
    public Ident $name; // function/method name
    public FuncType $type; // function signature: parameters, results, and position of "func" keyword
    public BlockStmt $body; // function body; or nil for external (non-Go) function

    public function declNode(): void
    {
        // TODO: Implement declNode() method.
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }
}
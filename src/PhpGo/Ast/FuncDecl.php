<?php declare(strict_types=1);

namespace PhpGo\Ast;

use InvalidArgumentException;
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

    public FieldList $recv; // receiver (methods); or nil (functions)
    public Ident $name;     // function/method name
    public FuncType $type;  // function signature: parameters, results, and position of "func" keyword
    public BlockStmt $body; // function body; or nil for external (non-Go) function

    public function declNode(): void
    {
        // TODO: Implement declNode() method.
    }

    public function tokenLiteral(): string
    {
        // TODO: Implement tokenLiteral() method.
    }

    public static function castFuncDecl(NodeInterface $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of BinaryExpr");
        }
        return $obj;
    }
}
<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class GoObject
 * @package PhpGo\Ast
 *
 * https://godoc.org/go/ast#Object
 */
final class GoObject
{
    public ObjectKind $kind;
    public string $name; // declared name
    // Decl interface{} // corresponding Field, XxxSpec, FuncDecl, LabeledStmt, AssignStmt, Scope; or nil
    // Data interface{} // object-specific data; or nil
    // Type interface{} // placeholder for type information; may be nil
}

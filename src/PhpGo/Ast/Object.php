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

    // The unresolved object is a sentinel to mark identifiers that have been added
    // to the list of unresolved identifiers. The sentinel is only used for verifying
    // internal consistency.
    // var unresolved = new(ast.Object)
    public const UNRESOLVED_OBJECT = 'UNRESOLVED_OBJECT';

    public static function unresolovedObject(): GoObject
    {
        $obj = new GoObject();
        $obj->name = self::UNRESOLVED_OBJECT;
        return $obj;
    }
}

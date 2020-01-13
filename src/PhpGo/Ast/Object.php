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
    public $decl; // corresponding Field, XxxSpec, FuncDecl, LabeledStmt, AssignStmt, Scope; or nil
    public $data; // object-specific data; or nil
    public $type; // placeholder for type information; may be nil

    // The unresolved object is a sentinel to mark identifiers that have been added
    // to the list of unresolved identifiers. The sentinel is only used for verifying
    // internal consistency.
    // var unresolved = new(ast.Object)
    public const UNRESOLVED_OBJECT = 'UNRESOLVED_OBJECT';

    /**
     * GoObject constructor.
     * @param ObjectKind $kind
     * @param string $name
     *
     * port from go/ast/NewObj
     */
    public function __construct(ObjectKind $kind, string $name)
    {
        $this->kind = $kind;
        $this->name = $name;
        $this->decl= null;
        $this->data = null;
        $this->type = null;
    }

    public static function unresolovedObject(): GoObject
    {
        return new GoObject(ObjectKind::kindBad(), self::UNRESOLVED_OBJECT);
    }
}

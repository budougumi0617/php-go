<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class Scope
 * @package PhpGo\Ast
 *
 * port from go/ast/Scope
 */
final class Scope
{
    public Scope $outer;
    /** @var array<string, GoObject> $objects * */
    public array $objects;

    /**
     * Scope constructor.
     * @param Scope $outer
     *
     * port from go/ast/Scope.NewScope
     */
    public function __construct(Scope $outer)
    {
        $this->outer = $outer;
        $this->objects = [];
    }

    /**
     * Lookup returns the object with the given name if it is
     * found in scope s, otherwise it returns nil. Outer scopes are ignored.
     *
     * @param string $name
     * @return GoObject
     *
     * port from go/ast/Scope.Lookup
     */
    public function lookup(string $name): GoObject
    {
        return $this->objects[$name];
    }

    /**
     * Insert attempts to insert a named object obj into the scope s.
     * If the scope already contains an object alt with the same name,
     * Insert leaves the scope unchanged and returns alt. Otherwise
     * it inserts obj and returns nil.
     * @param GoObject $obj
     * @return GoObject
     */
    public function insert(GoObject $obj): ?GoObject
    {
        if (array_key_exists($obj->name, $this->objects)) {
            return $this->objects[$obj->name];
        }
        $this->objects[$obj->name]= $obj;
        return null;
    }

    /**
     * Debugging support
     */
    public function string(): string
    {
        // var buf bytes.Buffer
        //    fmt.Fprintf(&buf, "scope %p {", s)
        //    if s != nil && len(s.Objects) > 0 {
        //    fmt.Fprintln(&buf)
        //    for _, obj := range s.Objects {
        //    fmt.Fprintf(&buf, "\t%s %s\n", obj.Kind, obj.Name)
        //    }
        //    }
        //    fmt.Fprintf(&buf, "}\n")
        //    return buf.String()
        //    }
        return '';
    }
}
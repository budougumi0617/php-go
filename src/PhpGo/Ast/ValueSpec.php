<?php declare(strict_types=1);

namespace PhpGo\Ast;

use PhpGo\Token\Token;

/**
 * Class ValueSpec
 * @package PhpGo\Ast
 *
 * https://godoc.org/go/ast#ValueSpec
 */
final class ValueSpec implements SpecInterface
{
    // Doc     *CommentGroup // associated documentation; or nil
    // Comment *CommentGroup // line comments; or nil
    public array $names; // array(Identifier). value names (len(Names) > 0)
    public ?ExpressionInterface $type; // value type; or nil
    public array $values; // array(ExpressionInterface). initial values; or nil

    public function __construct()
    {
        $this->names = [];
        $this->type = null;
        $this->values =[];
    }

    public function tokenLiteral(): string
    {
        $result = '';
        if (isset($this->type)) {
            $result .= $this->type->tokenLiteral();
        }
        if (count($this->names) > 0) {
            $result .= ' ' . $this->names[0]->tokenLiteral();
        }
        if (count($this->values) > 0) {
            $result .= ' ' . $this->values[0]->tokenLiteral();
        }
        return $result;
    }

    public function specNode(): void
    {
        // TODO: Implement specNode() method.
    }

}
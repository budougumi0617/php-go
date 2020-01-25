<?php declare(strict_types=1);

namespace PhpGo\Object;

use InvalidArgumentException;
use PhpGo\Ast\BasicLit;
use PhpGo\Ast\BlockStmt;
use PhpGo\Ast\FieldList;

final class FunctionObject implements GoObject
{
    /** @var array<BasicLit> $parameters */
    public array $parameters;
    public BlockStmt $body;
    public Scope $scope;

    /**
     * FunctionObject constructor.
     *
     * Ref: https://golang.org/pkg/go/ast/#FieldList
     *
     * @param array<BasicLit> $parameters
     * @param BlockStmt       $body
     * @param Scope           $scope
     */
    public function __construct(array $parameters, BlockStmt $body, Scope $scope)
    {
        $this->parameters = $parameters;
        $this->body = $body;
        $this->scope = $scope;
    }

    public static function castFunctionObject(GoObject $obj): self
    {
        if (!($obj instanceof self)) {
            throw new InvalidArgumentException("{$obj} is not instance of FunctionObject");
        }
        return $obj;
    }

    public function type(): ObjectType
    {
        return ObjectType::FUNCTION();
    }

    public function inspect(): string
    {
        // TODO: not implement it.
        return '';
    }
}
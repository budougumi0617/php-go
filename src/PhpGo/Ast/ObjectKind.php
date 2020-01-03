<?php declare(strict_types=1);

namespace PhpGo\Ast;

/**
 * Class ObjectKind
 * @package PhpGo\Ast
 *
 */
final class ObjectKind
{
    // https://godoc.org/go/ast#ObjKind
    public const BAD = 'for error handling';
    public const PKG = 'package';
    public const TYP = 'type';
    public const VAR = 'variable';
    public const FUN = 'function or method';
    public const LBL = 'label';
}

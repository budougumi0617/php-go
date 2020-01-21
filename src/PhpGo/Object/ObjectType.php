<?php declare(strict_types=1);

namespace PhpGo\Object;

use InvalidArgumentException;

/**
 * Class ObjectType
 *
 * @method static ObjectType INTEGER_OBJ()
 *
 * @package PhpGo\Object
 */
final class ObjectType
{
    private const INTEGER_OBJ = 'INTEGER_OBJ';

    private array $types = [
        self::INTEGER_OBJ,
    ];

    private string $type;


    public function __construct(string $type)
    {
        if (!in_array($type, $this->types, true)) {
            throw new InvalidArgumentException("Invalid type: {$type}");
        }

        $this->type = $type;
    }

    /**
     * @param $name string コールしようとしたメソッドの名前
     * @param $args string メソッド $name に渡そうとしたパラメータ
     *
     * @return self
     */
    public static function __callStatic($name, $args)
    {
        $class = get_called_class();
        $const = constant("$class::$name");
        return new $class($const);
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->type;
    }
}

<?php declare(strict_types=1);

namespace PhpGo\Token;

class Token
{
    public TokenType $type;
    public string $literal;
    private static $keywords = null;

    public function __construct(TokenType $type, string $literal)
    {
        $this->type = $type;
        $this->literal = $literal;
    }

    public function type(): TokenType
    {
        return $this->type;
    }

    public function string(): string
    {
        return "type: {$this->type->getType()} literal: \"{$this->literal}\"";
    }

    /**
     * go/token/Token.Lookup
     * @param string $ident
     * @return TokenType
     */
    public static function lookupIndent(string $ident): TokenType
    {
        $keywords = self::keymap();

        $type = $keywords[$ident];
        if ($type !== null) {
            return $type;
        }
        return new IdentType();
    }

    /**
     * @return array support token type by Lexer.
     */
    private static function keymap(): array
    {
        self::$keywords = self::$keywords ?? [
                "package" => new PackageType(),
                "import" => new ImportType(),
                "var" => new VarType(),
                "func" => new FuncType(),
                "return" => new ReturnType(),
            ];
        return self::$keywords;
    }
}

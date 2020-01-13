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

    /**
     * A set of constants for precedence-based expression parsing.
     * Non-operators have lowest precedence, followed by operators
     * starting with precedence 1 up to unary operators. The highest
     * precedence serves as "catch-all" precedence for selector,
     * indexing, and other operator and delimiter tokens.
     */
    public const LOWEST_PREC = 0; // non-operators
    public const UNARY_PREC = 6;
    public const HIGHEST_PREC = 7;

    public function precedence(): int
    {
        switch ($this->type->getType()) {
            case TokenType::T_LOR:
                return 1;
            case TokenType::T_LAND:
                return 2;
            case TokenType::T_EQL:
            case TokenType::T_NEQ:
            case TokenType::T_LSS:
            case TokenType::T_LEQ:
            case TokenType::T_GTR:
            case TokenType::T_GEQ:
                return 3;
            case TokenType::T_ADD:
            case TokenType::T_SUB:
            case TokenType::T_OR:
            case TokenType::T_XOR:
                return 4;
            case TokenType::T_MUL:
            case TokenType::T_QUO:
            case TokenType::T_REM:
            case TokenType::T_SHL:
            case TokenType::T_SHR:
            case TokenType::T_AND:
            case TokenType::T_AND_NOT:
                return 5;
        }
        return self::LOWEST_PREC;
    }
}

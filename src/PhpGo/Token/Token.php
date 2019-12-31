<?php declare(strict_types=1);

namespace PhpGo\Token;

class Token
{
    public TokenType $type;
    public string $literal;

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
}

/**
 * go/token/Token.Lookup
 * @param string $ident
 * @return TokenType
 */
function lookupIndent(string $ident): TokenType
{
    // FIXME: 関数呼び出しごとに初期化していい連想配列ではない。
    $keywords = [
        "package" => new PackageType(),
        "var" => new VarType(),
        "func" => new FuncType(),
        "return" => new ReturnType(),
    ];
    $type = $keywords[$ident];
    if ($type != null) {
        return $type;
    }
    return new IdentType();
}

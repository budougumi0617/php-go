<?php declare(strict_types=1);

namespace PhpGo\Token;

/**
 * Class TokenType
 * @package PhpGo\Token
 *
 * go/token/token.go
 */
abstract class TokenType
{
    protected string $type;

    public function getType(): string
    {
        return $this->type;
    }

    // Special tokens
    public const T_ILLEGAL = "ILLEGAL";
    public const T_EOF = "EOF";
    public const T_COMMENT = "COMMENT";

    // Identifiers and basic type literals
    // (these tokens stand for classes of literals)
    // literal_beg
    public const T_IDENT = "IDENT"; // main
    public const T_INT = "INT"; // 12345

    public const T_FLOAT = "FLOAT"; // 123.45
    public const T_IMAG = "IMAG"; // 123.45i
    public const T_CHAR = "CHAR"; // 'a'
    public const T_STRING = "STRING"; // "abc"
    // literal_end

    // operator_beg
    public const T_ASSIGN = "ASSIGN"; // =
    public const T_ADD = "ADD"; // +
    public const T_SUB = "SUB"; // -
    public const T_LPAREN = "LPAREN";// (
    public const T_LBRACK = "LBRACK";// [
    public const T_LBRACE = "LBRACE";// {
    public const T_COMMA = "COMMA";// ,
    public const T_PERIOD = "PERIOD";// .

    public const T_RPAREN = "RPAREN";   // )
    public const T_RBRACK = "RBRACK";  // ]
    public const T_RBRACE = "RBRACE";  // }
    public const T_SEMICOLON = "SEMICOLON";// ;
    public const T_COLON = "COLON";  // :
    // operator_end
}

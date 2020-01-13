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
    // Operators and delimiters
    public const T_ASSIGN = "ASSIGN"; // =
    public const T_ADD = "ADD"; // +
    public const T_SUB = "SUB"; // -

    public const T_ADD_ASSIGN = 'ADD_ASSIGN'; // +=
    public const T_SUB_ASSIGN = 'SUB_ASSIGN'; // -=
    public const T_MUL_ASSIGN = 'MUL_ASSIGN'; // *=
    public const T_QUO_ASSIGN = 'QUO_ASSIGN'; // /=
    public const T_REM_ASSIGN = 'REM_ASSIGN'; // %=

    public const T_AND_ASSIGN = 'AND_ASSIGN'; // &=
    public const T_OR_ASSIGN = 'OR_ASSIGN';   // |=
    public const T_XOR_ASSIGN = 'XOR_ASSIGN'; // ^=
    public const T_SHL_ASSIGN = 'SHL_ASSIGN'; // <<=
    public const T_SHR_ASSIGN = 'SHR_ASSIGN'; // >>=
    public const T_AND_NOT_ASSIGN = 'AND_NOT_ASSIGN'; // &^=

    public const T_DEFINE = "DEFINE";  // :=

    public const T_LPAREN = "LPAREN"; // ( Left parenthesis.
    public const T_LBRACK = "LBRACK"; // [ Left bracket.
    public const T_LBRACE = "LBRACE"; // { Left brace.
    public const T_COMMA = "COMMA";   // ,
    public const T_PERIOD = "PERIOD"; // .

    public const T_RPAREN = "RPAREN"; // ) Right parenthesis.
    public const T_RBRACK = "RBRACK"; // ] Right bracket.
    public const T_RBRACE = "RBRACE"; // } Right brace.
    public const T_SEMICOLON = "SEMICOLON"; // ;
    public const T_COLON = "COLON";  // :
    // operator_end

    // keyword_beg
    public const T_BREAK = "BREAK";  // break
    public const T_CASE = "CASE";  // case
    public const T_CHAN = "CHAN";  // chan
    public const T_CONST = "CONST";  // const
    public const T_CONTINUE = "CONTINUE";  // continue

    public const T_DEFAULT = "DEFAULT";  // default
    public const T_DEFER = "DEFER";  // defer
    public const T_ELSE = "ELSE";  // else
    public const T_FALLTHROUGH = "FALLTHROUGH";  // fallthrough
    public const T_FOR = "FOR";  // for

    public const T_FUNC = "FUNC";  // func
    public const T_GO = "GO";  // go
    public const T_GOTO = "GOTO";  // goto
    public const T_IF = "IF";  // if
    public const T_IMPORT = "IMPORT";  // import

    public const T_INTERFACE = "INTERFACE";  // interface
    public const T_MAP = "MAP";  // map
    public const T_PACKAGE = "PACKAGE";  // package
    public const T_RANGE = "RANGE";  // range
    public const T_RETURN = "RETURN";  // return

    public const T_SELECT = "SELECT";  // select
    public const T_STRUCT = "STRUCT";  // struct
    public const T_SWITCH = "SWITCH";  // switch
    public const T_TYPE = "TYPE";  // type
    public const T_VAR = "VAR";  // var
    // keyword_end
}

<?php

namespace Butonic\Syntax\Examples;

use Butonic\Syntax\Lexer;
use Butonic\Syntax\Token;

class ListLexer extends Lexer {
    const NAME      = 2;
    const COMMA     = 3;
    const LBRACK    = 4;
    const RBRACK    = 5;
    static $tokenNames = array("n/a", "<EOF>",
        "NAME", "COMMA",
        "LBRACK", "RBRACK" );

    public function getTokenName($x) {
        return self::$tokenNames[$x];
    }

    public function __construct($input) {
        parent::__construct($input);
    }

    public function isLETTER() {
        return $this->c >= 'a' &&
            $this->c <= 'z' ||
            $this->c >= 'A' &&
            $this->c <= 'Z';
    }

    public function nextToken() {
        while ( $this->c != self::EOF ) {
            switch ( $this->c ) {
                case ' ' :  case '\t': case '\n': case '\r': $this->WS();
                continue;
                case ',' : $this->consume();
                    return new Token($this, self::COMMA, ",");
                case '[' : $this->consume();
                    return new Token($this, self::LBRACK, "[");
                case ']' : $this->consume();
                    return new Token($this, self::RBRACK, "]");
                default:
                    if ($this->isLETTER() ) return $this->NAME();
                    throw new \Exception("invalid character: " + $this->c);
            }
        }
        return new Token($this, self::EOF_TYPE,"<EOF>");
    }

    /** NAME : ('a'..'z'|'A'..'Z')+; // NAME is sequence of >=1 letter */
    public function NAME() {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isLETTER());

        return new Token($this, self::NAME, $buf);
    }

    /** WS : (' '|'\t'|'\n'|'\r')* ; // ignore any whitespace */
    public function WS() {
        while(ctype_space($this->c)) {
            $this->consume();
        }
    }
}

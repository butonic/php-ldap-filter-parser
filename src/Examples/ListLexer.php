<?php

namespace Butonic\Syntax\Examples;

use Butonic\Syntax\Lexer;
use Butonic\Syntax\LexerException;
use Butonic\Syntax\Token;

class ListLexer extends Lexer {
    const NAME      = 'NAME';
    const COMMA     = 'COMMA';
    const LBRACK    = 'LBRACK';
    const RBRACK    = 'RBRACK';

    public function isLETTER() {
        return $this->c >= 'a' &&
            $this->c <= 'z' ||
            $this->c >= 'A' &&
            $this->c <= 'Z';
    }

    /**
     * @return Token
     * @throws LexerException
     */
    public function nextToken() {
        while ( $this->c !== self::EOF ) {
            switch ( $this->c ) {
                case ' ' :  case '\t': case '\n': case '\r': $this->WS();
                continue;
                case ',' : $this->consume();
                    return new Token(self::COMMA, ",");
                case '[' : $this->consume();
                    return new Token(self::LBRACK, "[");
                case ']' : $this->consume();
                    return new Token(self::RBRACK, "]");
                default:
                    if ($this->isLETTER() ) {
                        return $this->NAME();
                    }
                    throw new LexerException("invalid character: $this->c");
            }
        }
        return new Token(self::EOF_TYPE,"<EOF>");
    }

    /** NAME : ('a'..'z'|'A'..'Z')+; // NAME is sequence of >=1 letter */
    public function NAME() {
        $buf = '';
        do {
            $buf .= $this->c;
            $this->consume();
        } while ($this->isLETTER());

        return new Token(self::NAME, $buf);
    }

    /** WS : (' '|'\t'|'\n'|'\r')* ; // ignore any whitespace */
    public function WS() {
        while(ctype_space($this->c)) {
            $this->consume();
        }
    }
}

<?php

namespace Butonic\Syntax\LDAP;

use Butonic\Syntax\Lexer;
use Butonic\Syntax\Token;

/**
 * Class FilterLexer
 * @package Butonic\Syntax\LDAP
 * @see https://tools.ietf.org/html/rfc4515
 */
class FilterLexer extends Lexer implements Rfc4512 {

    const UTF1SUBSET = 'UTF1SUBSET';
    const EXCLAMATION = 'EXCLAMATION'; // = %x21 ; exclamation mark ("!")
    const PERCENT = 'PERCENT'; // = %x25 ; percent ("%")
    const AMPERSAND = 'AMPERSAND'; // = %x26 ; ampersand (or AND symbol) ("&")
    const ASTERISK = 'ASTERISK'; // == %x2A ; asterisk ("*")
    const SLASH = 'SLASH'; // == %x2F ; forward slash ("/")
    const COLON = 'COLON'; // = %x3A ; colon (":")
    const QUESTION = 'QUESTION'; // == %x3F ; question mark ("?")
    const AT = 'AT'; // == %x40 ; at ("@")
    const LSQUARE = 'LSQUARE'; // = %x5B ; left square bracket ("[")
    const RSQUARE = 'RSQUARE'; // = %x5D ; right square bracket ("]")
    const CARET = 'CARET'; // = %x5F ; caret ("^")
    const GRAVE = 'GRAVE'; // = %x60 ; grave accent ("`")
    const VERTBAR = 'VERTBAR'; // = %x7C ; vertical bar (or pipe) ("|")
    const TILDE = 'TILDE'; // = %x7E ; tilde ("~")

    /**
     * @param int $byte
     * @return bool
     */
    public function isUTF0($byte) {
        return ($byte >= 0x80) && ($byte <= 0xbf);
    }

    /**
     * @param int[] $bytes
     * @return bool
     */
    public function isUTF2(array $bytes) {
        return (count($bytes) === 2) && ($bytes[1] >= 0xc2) && ($bytes[1] <= 0xdf) && $this->isUTF0($bytes[2]);
    }

    /**
     * @param int[] $bytes
     * @return bool
     */
    public function isUTF3(array $bytes) {
        return count($bytes) === 3 && (
            ( // %xE0 %xA0-BF UTF0
                ($bytes[1] === 0xe0)
                && ($bytes[2] >= 0xa0) && ($bytes[2] <= 0xbf)
                && $this->isUTF0($bytes[3])
            ) || ( // %xE1-EC 2(UTF0)
                ($bytes[1] >= 0xe1) && ($bytes[1] <= 0xec)
                && $this->isUTF0($bytes[2])
                && $this->isUTF0($bytes[3])
            ) || ( // %xED %x80-9F UTF0
                ($bytes[1] === 0xed)
                && ($bytes[2] >= 0x80) && ($bytes[2] <= 0x9f)
                && $this->isUTF0($bytes[3])
            ) || ( // %xEE-EF 2(UTF0)
                ($bytes[1] >= 0xee) && ($bytes[1] <= 0xef)
                && $this->isUTF0($bytes[2])
                && $this->isUTF0($bytes[3])
            ));
    }

    /**
     * @param int[] $bytes
     * @return bool
     */
    public function isUTF4($bytes) {
        return count($bytes) === 4 && (
            ( // %xF0 %x90-BF 2(UTF0)
                ($bytes[1] === 0xf0)
                && ($bytes[2] >= 0x90) && ($bytes[2] <= 0xbf)
                && $this->isUTF0($bytes[3])
                && $this->isUTF0($bytes[4])
            ) || (// %xF1-F3 3(UTF0)
                ($bytes[1] >= 0xf1) && ($bytes[1] <= 0xf3)
                && $this->isUTF0($bytes[2])
                && $this->isUTF0($bytes[3])
                && $this->isUTF0($bytes[4])
            ) || (// %xF4 %x80-8F 2(UTF0)
                ($bytes[1] === 0xf4)
                && ($bytes[2] >= 0x80) && ($bytes[2] <= 0x8f)
                && $this->isUTF0($bytes[3])
                && $this->isUTF0($bytes[4])
            ));
    }

    /**
     * @param string $char
     * @return bool
     */
    public function isUTFMB($char) {
        $bytes = unpack('C*', $char); // BEWARE $bytes starts at 1!!!
        return $this->isUTF2($bytes)
            || $this->isUTF3($bytes)
            || $this->isUTF4($bytes);
    }

    /**
     * UTF1SUBSET     = %x01-27 / %x2B-5B / %x5D-7F
     *   ; UTF1SUBSET excludes 0x00 (NUL), LPAREN,
     *   ; RPAREN, ASTERISK, and ESC.
     * @param string $char
     * @return bool
     */
    public function isUTF1SUBSET($char) {
        if ($char === '') {
            return false;
        }
        $ord = mb_ord($char, 'utf-8');
        return (($ord >= 0x01) && ($ord <= 0x27))
            || (($ord >= 0x2b) && ($ord <= 0x5b))
            || (($ord >= 0x5d) && ($ord <= 0x7f));
    }

    /**
     * @param string $char
     * @return bool
     */
    public function isDIGIT($char) {
        return ($char >= '0') && ($char <= '9');
    }

    /**
     * @param string $char
     * @return bool
     */
    public function isALPHA($char) {
        return (($char >= 'a') && ($char <= 'z'))
            || (($char >= 'A') && ($char <= 'Z'));
    }

    public function nextToken() {
        while ( $this->c !== self::EOF ) {
            switch ( $this->c ) {
                case '(' : $this->consume();
                    return new Token(self::LPAREN, '(');
                case ')' : $this->consume();
                    return new Token(self::RPAREN, ')');
                case '&' : $this->consume();
                    return new Token(self::AMPERSAND, '&');
                case '|' : $this->consume();
                    return new Token(self::VERTBAR, '|');
                case '!' : $this->consume();
                    return new Token(self::EXCLAMATION, '!');
                case '*' : $this->consume();
                    return new Token(self::ASTERISK, '*');
                case '\\' : $this->consume();
                    return new Token(self::ESC, '\\');
                case ' ' : $this->consume();
                    return new Token(self::SPACE, ' ');
                case ',' : $this->consume();
                    return new Token(self::COMMA, ',');
                case '.' : $this->consume();
                    return new Token(self::DOT, '.');
                case '-' : $this->consume();
                    return new Token(self::HYPHEN, '.');
                case '=' : $this->consume();
                    return new Token(self::EQUALS, '=');
                case '~' : $this->consume();
                    return new Token(self::TILDE, '~');
                case '%' : $this->consume();
                    return new Token(self::PERCENT, '%');
                case '[' : $this->consume();
                    return new Token(self::LSQUARE, '[');
                case ']' : $this->consume();
                    return new Token(self::RSQUARE, ']');
                case ':' : $this->consume();
                    return new Token(self::COLON, ':');
                case ';' : $this->consume();
                    return new Token(self::SEMI, ';');
                default:
                    if ($this->isALPHA($this->c)) {
                        $token = new Token(self::ALPHA, $this->c);
                        $this->consume();
                        return $token;
                    }
                    if ($this->isDIGIT($this->c)) {
                        $token = new Token(self::DIGIT, $this->c);
                        $this->consume();
                        return $token;
                    }
                    if ($this->isUTF1SUBSET($this->c)) {
                        $token = new Token(self::UTF1SUBSET, $this->c);
                        $this->consume();
                        return $token;
                    }
                    if ($this->isUTFMB($this->c)) {
                        $token = new Token(self::UTFMB, $this->c);
                        $this->consume();
                        return $token;
                    }
                    throw new \Exception('invalid character: ' . $this->c);
            }
        }
        return new Token(self::EOF_TYPE,'<EOF>');
    }

}

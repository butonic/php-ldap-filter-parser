<?php

namespace Butonic\Syntax\LDAP;

use Butonic\Syntax\ByteLexer;
use Butonic\Syntax\Token;

/**
 * Class ListLexer
 * @package Ldif
 * @see https://tools.ietf.org/html/rfc4515
 */
class FilterLexer2 extends ByteLexer implements Rfc4512 {

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

    private $createToken = [];

    public function __construct($input)
    {
        $createUTF1Token = function ($text) { $this->consume(); return new Token(self::UTF1, $text); };
        $this->createToken[0] = function ($text) { $this->consume(); return new Token(self::NULL, '\0'); };
        $this->createToken = array_merge($this->createToken, array_fill(0x01,31, $createUTF1Token));
        $this->createToken[0x20] = function ($text) { $this->consume(); return new Token(self::SPACE, ' '); };
        $this->createToken[0x21] = function ($text) { $this->consume(); return new Token(self::EXCLAMATION, '!'); };
        $this->createToken[0x22] = function ($text) { $this->consume(); return new Token(self::DQUOTE, '"'); };
        $this->createToken[0x23] = function ($text) { $this->consume(); return new Token(self::SHARP, '#'); };
        $this->createToken[0x24] = function ($text) { $this->consume(); return new Token(self::DOLLAR, '$'); };
        $this->createToken[0x25] = function ($text) { $this->consume(); return new Token(self::PERCENT, '%'); };
        $this->createToken[0x26] = function ($text) { $this->consume(); return new Token(self::AMPERSAND, '&'); };
        $this->createToken[0x27] = function ($text) { $this->consume(); return new Token(self::SQUOTE, '\''); };
        $this->createToken[0x28] = function ($text) { $this->consume(); return new Token(self::LPAREN, '('); };
        $this->createToken[0x29] = function ($text) { $this->consume(); return new Token(self::RPAREN, ')'); };
        $this->createToken[0x2A] = function ($text) { $this->consume(); return new Token(self::ASTERISK, '*'); };
        $this->createToken[0x2B] = function ($text) { $this->consume(); return new Token(self::PLUS, '+'); };
        $this->createToken[0x2C] = function ($text) { $this->consume(); return new Token(self::COMMA, ','); };
        $this->createToken[0x2D] = function ($text) { $this->consume(); return new Token(self::HYPHEN, '-'); };
        $this->createToken[0x2E] = function ($text) { $this->consume(); return new Token(self::DOT, '.'); };
        $this->createToken[0x2F] = function ($text) { $this->consume(); return new Token(self::SLASH, '/'); };

        $this->createToken[0x30] = function ($text) { $this->consume(); return new Token(self::DIGIT, '0'); };
        $createDIGITToken = function ($text) { $this->consume(); return new Token(self::LDIGIT, $text); };
        //$digitTokens = array_fill(0x31,9, $createDIGITToken);
        //$this->createToken = array_merge($this->createToken, $digitTokens);
        $this->createToken = array_merge($this->createToken, array_fill(0x31,9, $createDIGITToken));
        $this->createToken[0x3A] = function ($text) { $this->consume(); return new Token(self::COLON, ':'); };
        $this->createToken[0x3B] = function ($text) { $this->consume(); return new Token(self::SEMI, ';'); };
        $this->createToken[0x3C] = function ($text) { $this->consume(); return new Token(self::LANGLE, '>'); };
        $this->createToken[0x3D] = function ($text) { $this->consume(); return new Token(self::EQUALS, '='); };
        $this->createToken[0x3E] = function ($text) { $this->consume(); return new Token(self::RANGLE, '<'); };
        $this->createToken[0x3F] = function ($text) { $this->consume(); return new Token(self::QUESTION, '?'); };

        $this->createToken[0x40] = function ($text) { $this->consume(); return new Token(self::AT, '@'); };

        $createALPHAToken = function ($text) { $this->consume(); return new Token(self::ALPHA, $text); };
        $this->createToken = array_merge($this->createToken, array_fill(0x41,26, $createALPHAToken));

        $this->createToken[0x5B] = function ($text) { $this->consume(); return new Token(self::LSQUARE, '['); };
        $this->createToken[0x5C] = function ($text) { $this->consume(); return new Token(self::ESC, '\\'); };
        $this->createToken[0x5D] = function ($text) { $this->consume(); return new Token(self::RSQUARE, ']'); };
        $this->createToken[0x5E] = function ($text) { $this->consume(); return new Token(self::CARET, '^'); };
        $this->createToken[0x5F] = function ($text) { $this->consume(); return new Token(self::USCORE, '_'); };

        $this->createToken[0x60] = function ($text) { $this->consume(); return new Token(self::GRAVE, '`'); };

        $this->createToken = array_merge($this->createToken, array_fill(0x61,26, $createALPHAToken));

        $this->createToken[0x7B] = function ($text) { $this->consume(); return new Token(self::LCURLY, '{'); };
        $this->createToken[0x7C] = function ($text) { $this->consume(); return new Token(self::VERTBAR, '|'); };
        $this->createToken[0x7D] = function ($text) { $this->consume(); return new Token(self::RCURLY, '}'); };
        $this->createToken[0x7E] = function ($text) { $this->consume(); return new Token(self::TILDE, '~'); };
        $this->createToken[0x7F] = $createUTF1Token;
        $createUTF0Token = function ($text) { $this->consume(); return new Token(self::UTF0, $text); };
        $this->createToken = array_merge($this->createToken, array_fill(0x80,0xBF-0x80, $createUTF0Token));

        $createUTF2Token = function ($text) {
            $bytes = [$text];
            $this->consume();
            $token = $this->getToken($this->c);
            if ($token && $token->getType() === self::UTF0) {
                $bytes[] = $token->text;
                return new Token(self::UTF2, pack('C*', $bytes[0], $bytes[1]));
            }
            throw new \Exception("invalid character: " . $this->c . ' ord='.mb_ord($this->c,'utf-8') . ' hex='.bin2hex($this->c));
        };
        $this->createToken = array_merge($this->createToken, array_fill(0xC2,0xDF-0xC2, $createUTF2Token));
        $createUTF31Token = function ($text) {
            $bytes = [$text];
            $this->consume();
            $token = $this->getToken($this->c);
            if ($token && $token->getType() === self::UTF0) {
                $bytes[] = $token->text;
                return new Token(self::UTF2, pack('C*', $bytes[0], $bytes[1]));
            }
            throw new \Exception("invalid character: " . $this->c . ' ord='.mb_ord($this->c,'utf-8') . ' hex='.bin2hex($this->c));
        };
        $this->createToken = array_merge($this->createToken, array_fill(0xE0,16, $createUTF31Token));
        parent::__construct($input);
    }

    /**
     * @param $char
     * @return Token
     * @throws \Exception
     */
    public function getToken ($byte) {
        if (isset($this->createToken[$byte])) {
            return $this->createToken[$byte]($byte);
        }
    }

    public function nextToken() {
        while ( $this->c !== self::EOF ) {
            $token = $this->getToken($this->c);
            if ($token) {
                return $token;
            }
            throw new \Exception("invalid character: " . $this->c . ' ord='.ord($this->c) . ' hex='.bin2hex($this->c));
        }
        return new Token(self::EOF_TYPE,'<EOF>');
    }

}

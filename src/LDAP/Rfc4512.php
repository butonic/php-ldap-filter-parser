<?php

namespace Butonic\Syntax\LDAP;


/**
 * Common ABNF Productions from https://tools.ietf.org/html/rfc4512#section-1.4
 */
interface Rfc4512 {
    const ALPHA = 'ALPHA'; // = %x41-5A / %x61-7A   ; "A"-"Z" / "a"-"z"
    const DIGIT  = 'DIGIT'; // %x30 / LDIGIT       ; "0"-"9"
    const LDIGIT = 'LDIGIT'; // = %x31-39             ; "1"-"9"
    const HEX = 'HEX'; // = DIGIT / %x41-46 / %x61-66 ; "0"-"9" / "A"-"F" / "a"-"f"

    const SP = 'SP'; // = 1*SPACE  ; one or more " "
    const WSP = 'WSP'; // = 0*SPACE  ; zero or more " "
    const NULL = 'NULL'; // = %x00 ; null (0)
    const SPACE = 'SPACE'; // = %x20 ; space (" ")
    const DQUOTE = 'DQUOTE'; // = %x22 ; quote (""")
    const SHARP = 'SHARP'; // = %x23 ; octothorpe (or sharp sign) ("#")
    const DOLLAR = 'DOLLAR'; // = %x24 ; dollar sign ("$")
    const SQUOTE = 'SQUOTE'; // = %x27 ; single quote ("'")
    const LPAREN = 'LPAREN'; // = %x28 ; left paren ("(")
    const RPAREN = 'RPAREN'; // = %x29 ; right paren (")")
    const PLUS = 'PLUS'; // = %x2B ; plus sign ("+")
    const COMMA = 'COMMA'; // = %x2C ; comma (",")
    const HYPHEN = 'HYPHEN'; // = %x2D ; hyphen ("-")
    const DOT = 'DOT'; // = %x2E ; period (".")
    const SEMI = 'SEMI'; // = %x3B ; semicolon (";")
    const LANGLE = 'LANGLE'; // = %x3C ; left angle bracket ("<")
    const EQUALS = 'EQUALS'; // = %x3D ; equals sign ("=")
    const RANGLE = 'RANGLE'; // = %x3E ; right angle bracket (">")
    const ESC = 'ESC'; // = %x5C ; backslash ("\")
    const USCORE = 'USCORE'; // = %x5F ; underscore ("_")
    const LCURLY = 'LCURLY'; // = %x7B ; left curly brace "{"
    const RCURLY = 'RCURLY'; // = %x7D ; right curly brace "}"

      //; Any UTF-8 [RFC3629] encoded Unicode [Unicode] character
    const UTF8 = 'UTF8'; // = UTF1 / UTFMB
    const UTFMB = 'UTFMB'; // = UTF2 / UTF3 / UTF4
    const UTF0 = 'UTF0'; // = %x80-BF
    const UTF1 = 'UTF1'; // = %x00-7F
    const UTF2 = 'UTF2'; // = %xC2-DF UTF0
    const UTF3 = 'UTF3'; // = %xE0 %xA0-BF UTF0 / %xE1-EC 2(UTF0) /
                 //   %xED %x80-9F UTF0 / %xEE-EF 2(UTF0)
    const UTF4 = 'UTF4'; // = %xF0 %x90-BF 2(UTF0) / %xF1-F3 3(UTF0) /
                //    %xF4 %x80-8F 2(UTF0)

    const OCTET = 'OCTET'; // = %x00-FF ; Any octet (8-bit data unit)
}
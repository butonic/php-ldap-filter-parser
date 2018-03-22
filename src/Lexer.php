<?php

namespace Butonic\Syntax;

abstract class Lexer {

    const EOF       = ''; // represent end of file char
    const EOF_TYPE  = null;  // represent EOF token type

    protected $input;     // input string
    protected $len;     // input string
    protected $p = -1;     // index into input of current character
    protected $c;         // current character

    public function __construct($input) {
        $this->input = $input;
        $this->len = mb_strlen($this->input);
        // prime lookahead
        $this->consume();
    }

    /** Move one character; detect "end of file" */
    public function consume() {
        $this->p++;
        if ($this->p >= $this->len) {
            $this->c = self::EOF;
        } else {
            // TODO ouch this is slow: http://php.net/manual/en/function.mb-substr.php#117764
            $this->c = mb_substr($this->input, $this->p, 1);
        }
    }

    public function getPos() {
        return $this->p;
    }

    /**
     * @return Token
     */
    abstract public function nextToken();
}

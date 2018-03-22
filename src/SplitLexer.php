<?php

namespace Butonic\Syntax;

abstract class SplitLexer {

    const EOF       = ''; // represent end of file char
    const EOF_TYPE  = null;  // represent EOF token type

    protected $input;     // input string
    protected $len;     // input string
    protected $p = -1;     // index into input of current character
    protected $c;         // current character

    public function __construct($input) {
        $this->input = preg_split('//u', $input, null, PREG_SPLIT_NO_EMPTY);
        $this->len = count($this->input);
        // prime lookahead
        $this->consume();
    }

    /** Move one character; detect "end of file" */
    public function consume() {
        $this->p++;
        if ($this->p >= $this->len) {
            $this->c = self::EOF;
        } else {
            $this->c = $this->input[$this->p];
        }
    }

    abstract public function nextToken();
}

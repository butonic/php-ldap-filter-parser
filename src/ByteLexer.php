<?php

namespace Butonic\Syntax;

abstract class ByteLexer {

    const EOF       = 'EOF'; // represent end of file char
    const EOF_TYPE  = 'EOF_TYPE';  // represent EOF token type

    protected $bytes;     // input string as bytes, starts at 1, see unpack doc
    protected $p = 0;     // index into input of current character
    protected $c;         // current character

    public function __construct($input) {
        $this->bytes = unpack('C*', $input);
        // prime lookahead
        $this->consume();
    }

    /** Move one character; detect "end of file" */
    public function consume() {
        $this->p++;
        if ($this->p > count($this->bytes)) {
            $this->c = self::EOF;
        } else {
            $this->c = $this->bytes[$this->p];
        }
    }

    abstract public function nextToken();
}

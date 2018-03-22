<?php

namespace Butonic\Syntax;

class Token {

    /**
     * @var Lexer
     */
    public $lexer;

    /**
     * @var int
     */
    public $type;

    /**
     * @var string
     */
    public $text;

    /**
     * Token constructor.
     * @param int $type
     * @param string $text
     */
    public function __construct($type, $text) {
        $this->type = $type;
        $this->text = $text;
    }

    public function __toString() {
        return "<'$this->text', $this->type>";
    }

    public function getType() {
        return $this->type;
    }
}
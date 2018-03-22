<?php

namespace Butonic\Syntax;

abstract class Parser {
    /** @var Lexer */
    public $lexer;     // from where do we get tokens?

    /** @var Token */
    public $lookahead; // the current lookahead token

    public function __construct(Lexer $lexer) {
        $this->lexer = $lexer;
        $this->consume();
    }

    /** If lookahead token type matches x, consume & return else error */
    public function match($x) {
        if ($this->lookahead->type === $x ) {
            $this->consume();
        } else {
            throw new \Exception("Expecting token " .
                $x . ":Found " . $this->lookahead);
        }
    }
    public function consume() {
        $this->lookahead = $this->lexer->nextToken();
    }
}
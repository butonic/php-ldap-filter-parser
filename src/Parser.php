<?php

namespace Butonic\Syntax;

abstract class Parser {
    /** @var Lexer */
    public $lexer;     // from where do we get tokens?

    /** @var Token */
    public $lookahead; // the current lookahead token

    /**
     * Parser constructor.
     * @param Lexer $lexer
     * @throws LexerException
     */
    public function __construct(Lexer $lexer) {
        $this->lexer = $lexer;
        $this->consume();
    }

    /** If lookahead token type matches x, consume & return else error
     * @param $type
     * @throws SyntaxException
     */
    public function match($type) {
        if ($this->lookahead->type === $type ) {
            $this->consume();
        } else {
            throw new ParserException("Expecting token $type: Found $this->lookahead");
        }
    }

    /**
     * @throws LexerException
     */
    public function consume() {
        $this->lookahead = $this->lexer->nextToken();
    }
}
<?php

namespace Butonic\Syntax\Examples;

use Butonic\Syntax\Parser;
use Butonic\Syntax\ParserException;
use Butonic\Syntax\SyntaxException;

class ListParser extends Parser {

    /**
     * list : '[' elements ']' ; // match bracketed list
     * @throws SyntaxException
     */
    public function rlist() {
        $this->match(ListLexer::LBRACK);
        $this->elements();
        $this->match(ListLexer::RBRACK);
    }

    /**
     * elements : element (',' element)* ;
     * @throws SyntaxException
     */
    public function elements() {
        $this->element();
        while ($this->lookahead->type === ListLexer::COMMA ) {
            $this->match(ListLexer::COMMA);
            $this->element();
        }
    }

    /**
     * element : name | list ; // element is name or nested list
     * @throws SyntaxException
     */
    public function element() {
        if ($this->lookahead->type === ListLexer::NAME ) {
            $this->match(ListLexer::NAME);
        }
        else if ($this->lookahead->type === ListLexer::LBRACK) {
            $this->rlist();
        }
        else {
            throw new ParserException("Expecting name or list, found $this->lookahead");
        }
    }
}

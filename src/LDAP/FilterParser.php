<?php

namespace Butonic\Syntax\LDAP;

use Butonic\Syntax\Parser;

/**
 * Class FilterParser
 * @package Butonic\Syntax\LDAP
 * @see https://tools.ietf.org/html/rfc4515
 */
class FilterParser extends Parser {

    /**
     * LPAREN filtercomp RPAREN
     * @throws \Exception
     */
    public function filter() {
        $this->match(FilterLexer::LPAREN);
        $this->filtercomp();
        $this->match(FilterLexer::RPAREN);
    }

    /**
     * and / or / not / item
     * @throws \Exception
     */
    public function filtercomp() {
        switch ($this->lookahead->type) {
            case (FilterLexer::AMPERSAND):
                $this->andFilter();
                return;
            case (FilterLexer::VERTBAR):
                $this->orFilter();
                return;
            case (FilterLexer::EXCLAMATION):
                $this->not();
                return;
            default:
                $this->item();
        }
    }

    /**
     * AMPERSAND filterlist
     * @throws \Exception
     */
    public function andFilter() {
        $this->match(FilterLexer::AMPERSAND);
        $this->filterlist();
    }

    /**
     * VERTBAR filterlist
     * @throws \Exception
     */
    public function orFilter() {
        $this->match(FilterLexer::VERTBAR);
        $this->filterlist();
    }

    /**
     * EXCLAMATION filterlist
     * @throws \Exception
     */
    public function not() {
        $this->match(FilterLexer::EXCLAMATION);
        $this->filterlist();
    }

    /**
     * simple / present / substring / extensible
     * @throws \Exception
     */
    public function item() {
        if ($this->lookahead->type === FilterLexer::ALPHA
            || $this->lookahead->type === FilterLexer::DIGIT
        ) {
            $this->attr();
            $this->itemTail();
        } else {
            $this->extensible2();
        }
    }

    /**
     * simpleTail / presentTail / substringTail / extensible1Tail
     *
     * simpleTail = filtertype assertionvalue
     *     filtertype = equal / approx / greaterorequal / lessorequal
     *
     * presentTail = EQUALS ASTERISK
     *
     * substringTail = EQUALS [initial] any [final]
     *
     * extensible1Tail     = [dnattrs] [matchingrule] COLON EQUALS assertionvalue
     *     dnattrs        = COLON "dn"
     *     matchingrule   = COLON oid
     *
     * @throws \Exception
     */
    public function itemTail() {
        switch ($this->lookahead->type) {
            case (FilterLexer::COLON):
                $this->extensible1Tail();
                return;
            case (FilterLexer::TILDE):
            case (FilterLexer::RANGLE):
            case (FilterLexer::LANGLE):
                $this->simpleTail();
                return;
            case (FilterLexer::EQUALS):
                $this->consume();
                /*
                if ($this->lookahead->type === FilterLexer::ASTERISK) {
                    $this->consume(); // = present matched
                    return;
                }
                */
                // simpleTail2 = assertionvalue === initial // can be empty
                $this->assertionvalue(); // = simple matched
                // substringTail2 =  [initial] any [final]
                while ($this->lookahead->type === FilterLexer::ASTERISK) {
                    $this->consume();
                    $this->assertionvalue(); // = substring matched
                }
                return;
            default:
                throw new \Exception("Expecting filtertype : Found $this->lookahead");
        }
    }
    /**
     * simpleTail = filtertype assertionvalue
     * @throws \Exception
     */
    public function simpleTail() {
        $this->filtertype();
        $this->assertionvalue();
    }

    /**
     * extensible1Tail     = [dnattrs] [matchingrule] COLON EQUALS assertionvalue
     * dnattrs        = COLON "dn"
     * matchingrule   = COLON oid
     * @throws \Exception
     */
    public function extensible1Tail() {
        $this->match(FilterLexer::COLON);
        // dnattrs optional
        if ($this->lookahead->text === 'd' || $this->lookahead->text === 'D' ) {
            $this->consume();
            if ($this->lookahead->text === 'n' || $this->lookahead->text === 'N' ) {
                $this->consume();
                $this->match(FilterLexer::COLON); // must end in [matchingrule] COLON EQUALS assertionvalue
            } else {
                throw new \Exception("Expecting dnattrs : Found $this->lookahead");
            }
        }
        // matchingrule optional
        if ($this->lookahead->type === FilterLexer::ALPHA
            || $this->lookahead->type === FilterLexer::DIGIT) {
            $this->oid();
            $this->match(FilterLexer::COLON); // must end in COLON EQUALS assertionvalue
        }

        $this->match(FilterLexer::EQUALS);
        $this->assertionvalue();
    }
    /**
        extensible2     = [dnattrs] matchingrule COLON EQUALS assertionvalue
     * dnattrs        = COLON "dn"
     * matchingrule   = COLON oid
     * @throws \Exception
     */
    public function extensible2() {
        $this->match(FilterLexer::COLON);
        // dnattrs optional
        if ($this->lookahead->text === 'd' || $this->lookahead->text === 'D' ) {
            $this->consume();
            if ($this->lookahead->text === 'n' || $this->lookahead->text === 'N' ) {
                $this->consume();
                $this->match(FilterLexer::COLON); // must end in matchingrule COLON EQUALS assertionvalue
            } else {
                throw new \Exception("Expecting dnattrs : Found $this->lookahead");
            }
        }
        // matchingrule NOT optional
        $this->oid();

        // must end in COLON EQUALS assertionvalue
        $this->match(FilterLexer::COLON);
        $this->match(FilterLexer::EQUALS);
        $this->assertionvalue();
    }

    /**
     * filtertype = equal / approx / greaterorequal / lessorequal
     * @throws \Exception
     */
    public function filtertype() {
        switch ($this->lookahead->type) {
            case (FilterLexer::EQUALS):
                $this->consume();
                return;
            case (FilterLexer::TILDE):
                $this->approx();
                return;
            case (FilterLexer::RANGLE):
                $this->greaterorequal();
                return;
            case (FilterLexer::LANGLE):
                $this->lessorequal();
                return;
            default:
                throw new \Exception("Expecting filtertype : Found $this->lookahead");
        }
    }

    /**
     * TILDE EQUALS
     * @throws \Exception
     */
    public function approx() {
        $this->match(FilterLexer::TILDE);
        $this->match(FilterLexer::EQUALS);
    }

    /**
     * RANGLE EQUALS
     * @throws \Exception
     */
    public function greaterorequal() {
        $this->match(FilterLexer::RANGLE);
        $this->match(FilterLexer::EQUALS);
    }

    /**
     * LANGLE EQUALS
     * @throws \Exception
     */
    public function lessorequal() {
        $this->match(FilterLexer::LANGLE);
        $this->match(FilterLexer::EQUALS);
    }

    /**
     * 1*filter
     * @throws \Exception
     */
    public function filterlist() {
        do {
            $this->filter();
        } while ($this->lookahead->type === FilterLexer::LPAREN);
    }

    /**
     * number  = DIGIT / ( LDIGIT 1*DIGIT )
     * @throws \Exception
     */
    public function number () {
        if ($this->lookahead->type === FilterLexer::DIGIT) {
            if ($this->lookahead->text === '0') { // single DIGIT
                $this->consume();
            } else {
                // LDIGIT 1*DIGIT
                do {
                    $this->consume();
                } while ($this->lookahead->type === FilterLexer::DIGIT);
            }
        } else {
            throw new \Exception("Expecting number : Found $this->lookahead");
        }
    }
    /**
     * numericoid = number 1*( DOT number )
     * @throws \Exception
     */
    public function numericoid () {
        if ($this->lookahead->type === FilterLexer::DIGIT) {
            $this->number();
            while ($this->lookahead->type === FilterLexer::DOT) { // single DIGIT
                $this->consume();
                $this->number();
            }
        } else {
            throw new \Exception("Expecting numericoid : Found $this->lookahead");
        }
    }

    /**
     * @throws \Exception
     */
    public function keychar() {
        switch ($this->lookahead->type) {
            case FilterLexer::ALPHA:
            case FilterLexer::DIGIT:
            case FilterLexer::HYPHEN:
                $this->consume();
                return;
            default:
                throw new \Exception("Expecting ALPHA / DIGIT / HYPHEN : Found $this->lookahead");
        }
    }
    /**
     * leadkeychar *keychar
     * leadkeychar = ALPHA
     * keychar = ALPHA / DIGIT / HYPHEN
     * @throws \Exception
     */
    public function keystring () {
        if ($this->lookahead->type === FilterLexer::ALPHA) {
            $this->match(FilterLexer::ALPHA);
        } else {
            throw new \Exception("Expecting ALPHA : Found $this->lookahead");
        }
        while (true) {
            switch ($this->lookahead->type) {
                case FilterLexer::ALPHA:
                case FilterLexer::DIGIT:
                case FilterLexer::HYPHEN:
                    $this->keychar();
                    break;
                default:
                    break 2;
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function descr () {
        $this->keystring();
    }

    /**
     * @throws \Exception
     */
    public function oid() {
        if ($this->lookahead->type === FilterLexer::ALPHA ) {
            $this->descr();
        } else if ($this->lookahead->type === FilterLexer::DIGIT) {
            $this->numericoid();
        } else {
            throw new \Exception("Expecting descr or numericid: Found $this->lookahead");
        }
    }


    /**
        option = 1*keychar
     * @throws \Exception
     */
    public function option() {
        do {
            $this->keychar();
        } while (
            $this->lookahead->type === FilterLexer::ALPHA
            || $this->lookahead->type === FilterLexer::DIGIT
            || $this->lookahead->type === FilterLexer::HYPHEN
        );
    }
    /**
        options = *( SEMI option )
     * @throws \Exception
     */
    public function options() {
        while($this->lookahead->type === FilterLexer::SEMI) {
            $this->consume();
            $this->option();
        }
    }

    /**
     * attributetype = oid
     * @throws \Exception
     */
    public function attributetype() {
        $this->oid();
    }
    /**
     * attributedescription = attributetype options
     * @throws \Exception
     */
    public function attributedescription() {
        $this->attributetype();
        $this->options();
    }

    /**
     * attr EQUALS [initial] any [final]
     * @throws \Exception
     */
    public function substring() {
        $this->attr();
        $this->substringTail();
    }
    /**
     * substringTail = EQUALS [initial] any [final]
     * @throws \Exception
     */
    public function substringTail() {
        $this->match(FilterLexer::EQUALS);
        $this->initial();
        $this->any();
        $this->fin();
    }

    /**
     * @throws \Exception
     */
    public function initial() {
        $this->assertionvalue();
    }

    /**
     * any            = ASTERISK *(assertionvalue ASTERISK)
     * @throws \Exception
     */
    public function any() {
        $this->match(FilterLexer::ASTERISK);
        while ($this->lexer->isUTF1SUBSET($this->lookahead->text)) {
            $this->assertionvalue();
            $this->match(FilterLexer::ASTERISK);
        }
    }

    /**
     * @throws \Exception
     */
    public function fin() { // final can only be used in php 7
        $this->assertionvalue();
    }

    /**
     * @throws \Exception
     */
    public function attr() {
        $this->attributedescription();
    }

    /**
     * dnattrs        = COLON "dn"
     * @throws \Exception
     */
    public function dnattrs() {
        $this->match(FilterLexer::COLON);
        if ($this->lookahead->text === 'd' || $this->lookahead->text === 'D' ) {
            $this->consume();
            if ($this->lookahead->text === 'n' || $this->lookahead->text === 'N' ) {
                $this->consume();
                return;
            }
        }
        throw new \Exception("Expecting dnattr: Found $this->lookahead");
    }

    /**
     * matchingrule   = COLON oid
     * @throws \Exception
     */
    public function matchingrule() {
        $this->match(FilterLexer::COLON);
        $this->oid();
    }

    /**
     * @throws \Exception
     */
    public function assertionvalue() {
        $this->valueencoding();
    }

    /**
     * 0*(normal / escaped)
     * @throws \Exception
     */
    public function valueencoding() {
        while (true) {
            if ($this->lexer->isUTF1SUBSET($this->lookahead->text)) {
                $this->consume();
                continue;
            }
            if ($this->lookahead->type === FilterLexer::ESC) {
                $this->escaped();
                continue;
            }
            break;
        }
    }

    /**
     * @throws \Exception
     */
    public function normal() {
        switch ($this->lookahead->type) {
            case FilterLexer::ALPHA:
            case FilterLexer::DIGIT:
            case FilterLexer::AMPERSAND:
            case FilterLexer::VERTBAR:
            case FilterLexer::EXCLAMATION:
            case FilterLexer::SPACE:
            case FilterLexer::COMMA:
            case FilterLexer::EQUALS:
            case FilterLexer::TILDE:
            case FilterLexer::PERCENT:
            case FilterLexer::LSQUARE:
            case FilterLexer::RSQUARE:
            case FilterLexer::COLON:
            case FilterLexer::SEMI:
            case FilterLexer::UTF1SUBSET:
            case FilterLexer::UTFMB:
                $this->consume();
                return;
            default:
                throw new \Exception("Expecting UTF1SUBSET or UTFMB : Found $this->lookahead");
        }
    }

    /**
     * @throws \Exception
     */
    public function escaped() {
        $this->match(FilterLexer::ESC);
        $this->HEX();
        $this->HEX();
    }

    /**
     * @throws \Exception
     */
    public function HEX() {
        if ($this->lookahead->type === FilterLexer::DIGIT) {
            $this->match(FilterLexer::DIGIT);
        } else {
            if ($this->lookahead->type === FilterLexer::ALPHA
                && (($this->lookahead->text >= 'a' && $this->lookahead->text <= 'f')
                || ($this->lookahead->text >= 'A' && $this->lookahead->text <= 'Z'))
            ) {
                $this->consume();
            } else {
                throw new \Exception("Expecting a-z or A-Z : Found $this->lookahead");
            }
        }
    }
}

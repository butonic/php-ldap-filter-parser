<?php

namespace Butonic\Syntax\LDAP;

use Butonic\Syntax\Parser;
use Butonic\Syntax\ParserException;
use Butonic\Syntax\SyntaxException;

/**
 * Class FilterParser
 * @package Butonic\Syntax\LDAP
 * @see https://tools.ietf.org/html/rfc4515
 */
class FilterParser extends Parser {

    /**
     * filter = LPAREN filtercomp RPAREN
     * @throws SyntaxException
     */
    public function filter() {
        $this->match(FilterLexer::LPAREN);
        $this->filtercomp();
        $this->match(FilterLexer::RPAREN);
    }

    /**
     * filtercomp = and / or / not / item
     * @throws SyntaxException
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
     * and = AMPERSAND filterlist
     * @throws SyntaxException
     */
    public function andFilter() {
        $this->match(FilterLexer::AMPERSAND);
        $this->filterlist();
    }

    /**
     * or = VERTBAR filterlist
     * @throws SyntaxException
     */
    public function orFilter() {
        $this->match(FilterLexer::VERTBAR);
        $this->filterlist();
    }

    /**
     * not = EXCLAMATION filterlist
     * @throws SyntaxException
     */
    public function not() {
        $this->match(FilterLexer::EXCLAMATION);
        $this->filterlist();
    }

    /**
     * item = simple / present / substring / extensible
     * @throws SyntaxException
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
     * itemTail         = simpleTail / presentTail / substringTail / extensible1Tail
     *
     * simpleTail       = filtertype assertionvalue
     *     filtertype   = equal / approx / greaterorequal / lessorequal
     *
     * presentTail      = EQUALS ASTERISK
     *
     * substringTail    = EQUALS [initial] any [final]
     *
     * extensible1Tail  = [dnattrs] [matchingrule] COLON EQUALS assertionvalue
     *     dnattrs      = COLON "dn"
     *     matchingrule = COLON oid
     *
     * @throws SyntaxException
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
                // simpleTail2 = assertionvalue === initial // can be empty
                $this->assertionvalue(); // = simple matched
                // substringTail2 =  [initial] any [final]
                while ($this->lookahead->type === FilterLexer::ASTERISK) {
                    $this->consume();
                    $this->assertionvalue(); // = substring matched
                }
                return;
            default:
                throw new ParserException("Expecting filtertype, found $this->lookahead");
        }
    }

    /**
     * simpleTail = filtertype assertionvalue
     * @throws SyntaxException
     */
    public function simpleTail() {
        $this->filtertype();
        $this->assertionvalue();
    }

    /**
     * extensible1Tail = [dnattrs] [matchingrule] COLON EQUALS assertionvalue
     * dnattrs         = COLON "dn"
     * matchingrule    = COLON oid
     * @throws SyntaxException
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
                throw new ParserException("Expecting dnattrs, found $this->lookahead");
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
     * extensible2  = [dnattrs] matchingrule COLON EQUALS assertionvalue
     * dnattrs      = COLON "dn"
     * matchingrule = COLON oid
     * @throws SyntaxException
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
                throw new ParserException("Expecting dnattrs, found $this->lookahead");
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
     * @throws SyntaxException
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
                throw new ParserException("Expecting filtertype, found $this->lookahead");
        }
    }

    /**
     * approx = TILDE EQUALS
     * @throws SyntaxException
     */
    public function approx() {
        $this->match(FilterLexer::TILDE);
        $this->match(FilterLexer::EQUALS);
    }

    /**
     * greaterorequal = RANGLE EQUALS
     * @throws SyntaxException
     */
    public function greaterorequal() {
        $this->match(FilterLexer::RANGLE);
        $this->match(FilterLexer::EQUALS);
    }

    /**
     * lessorequal = LANGLE EQUALS
     * @throws SyntaxException
     */
    public function lessorequal() {
        $this->match(FilterLexer::LANGLE);
        $this->match(FilterLexer::EQUALS);
    }

    /**
     * filterlist = 1*filter
     * @throws SyntaxException
     */
    public function filterlist() {
        do {
            $this->filter();
        } while ($this->lookahead->type === FilterLexer::LPAREN);
    }

    /**
     * number = DIGIT / ( LDIGIT 1*DIGIT )
     * @throws SyntaxException
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
            throw new ParserException("Expecting number, found $this->lookahead");
        }
    }

    /**
     * numericoid = number 1*( DOT number )
     * @throws SyntaxException
     */
    public function numericoid () {
        if ($this->lookahead->type === FilterLexer::DIGIT) {
            $this->number();
            while ($this->lookahead->type === FilterLexer::DOT) { // single DIGIT
                $this->consume();
                $this->number();
            }
        } else {
            throw new ParserException("Expecting numericoid, found $this->lookahead");
        }
    }

    /**
     * keychar = ALPHA / DIGIT / HYPHEN
     * @throws SyntaxException
     */
    public function keychar() {
        switch ($this->lookahead->type) {
            case FilterLexer::ALPHA:
            case FilterLexer::DIGIT:
            case FilterLexer::HYPHEN:
                $this->consume();
                return;
            default:
                throw new ParserException("Expecting ALPHA / DIGIT / HYPHEN, found $this->lookahead");
        }
    }

    /**
     * keystring   = leadkeychar *keychar
     * leadkeychar = ALPHA
     * keychar     = ALPHA / DIGIT / HYPHEN
     * @throws SyntaxException
     */
    public function keystring () {
        if ($this->lookahead->type === FilterLexer::ALPHA) {
            $this->match(FilterLexer::ALPHA);
        } else {
            throw new ParserException("Expecting ALPHA, found $this->lookahead");
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
     * descr = keystring
     * @throws SyntaxException
     */
    public function descr () {
        $this->keystring();
    }

    /**
     * oid = descr / numericoid
     * @throws SyntaxException
     */
    public function oid() {
        if ($this->lookahead->type === FilterLexer::ALPHA ) {
            $this->descr();
        } else if ($this->lookahead->type === FilterLexer::DIGIT) {
            $this->numericoid();
        } else {
            throw new ParserException("Expecting descr or numericid, found $this->lookahead");
        }
    }

    /**
     * option = 1*keychar
     * @throws SyntaxException
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
     * options = *( SEMI option )
     * @throws SyntaxException
     */
    public function options() {
        while($this->lookahead->type === FilterLexer::SEMI) {
            $this->consume();
            $this->option();
        }
    }

    /**
     * attributetype = oid
     * @throws SyntaxException
     */
    public function attributetype() {
        $this->oid();
    }

    /**
     * attributedescription = attributetype options
     * @throws SyntaxException
     */
    public function attributedescription() {
        $this->attributetype();
        $this->options();
    }

    /**
     * attr EQUALS [initial] any [final]
     * @throws SyntaxException
     */
    public function substring() {
        $this->attr();
        $this->substringTail();
    }

    /**
     * substringTail = EQUALS [initial] any [final]
     * @throws SyntaxException
     */
    public function substringTail() {
        $this->match(FilterLexer::EQUALS);
        $this->initial();
        $this->any();
        $this->fin();
    }

    /**
     * initial = assertionvalue
     * @throws SyntaxException
     */
    public function initial() {
        $this->assertionvalue();
    }

    /**
     * any = ASTERISK *(assertionvalue ASTERISK)
     * @throws SyntaxException
     */
    public function any() {
        $this->match(FilterLexer::ASTERISK);
        while ($this->lexer->isUTF1SUBSET($this->lookahead->text)) {
            $this->assertionvalue();
            $this->match(FilterLexer::ASTERISK);
        }
    }

    /**
     * fin = assertionvalue
     * @throws SyntaxException
     */
    public function fin() { // final can only be used in php 7
        $this->assertionvalue();
    }

    /**
     * attr = attributedescription
     * @throws SyntaxException
     */
    public function attr() {
        $this->attributedescription();
    }

    /**
     * dnattrs = COLON "dn"
     * @throws SyntaxException
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
        throw new ParserException("Expecting dnattrs, found $this->lookahead");
    }

    /**
     * matchingrule = COLON oid
     * @throws SyntaxException
     */
    public function matchingrule() {
        $this->match(FilterLexer::COLON);
        $this->oid();
    }

    /**
     * assertionvalue = valueencoding
     * @throws SyntaxException
     */
    public function assertionvalue() {
        $this->valueencoding();
    }

    /**
     * valueencoding = 0*(normal / escaped)
     * @throws SyntaxException
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
     * normal = UTF1SUBSET / UTFMB
     * the other Tokens are here because the lexer recognizes them as well, UTF1SUBSET is a "catchall"
     * @throws SyntaxException
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
                throw new ParserException("Expecting UTF1SUBSET or UTFMB, found $this->lookahead");
        }
    }

    /**
     * escaped = ESC HEX HEX
     * @throws SyntaxException
     */
    public function escaped() {
        $this->match(FilterLexer::ESC);
        $this->HEX();
        $this->HEX();
    }

    /**
     * HEX = a-z / A-Z / DIGIT
     * @throws SyntaxException
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
                throw new ParserException("Expecting a-z or A-Z, found $this->lookahead");
            }
        }
    }
}

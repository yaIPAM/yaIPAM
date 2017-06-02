<?php
/**
 * MatchAgainst
 *
 * Definition for MATCH AGAINST MySQL instruction to be used in DQL Queries
 *
 * Usage: MATCH_AGAINST(column[, column, ;;.], :text)
 *
 * @author jeremy.hubert@infogroom.fr
 * using work of http://groups.google.com/group/doctrine-user/browse_thread/thread/69d1f293e8000a27
 */
namespace Application\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * "MATCH_AGAINST" "(" {StateFieldPathExpression ","}* Literal ")"
 */
class MatchAgainstFunction extends FunctionNode {

    public $columns = array();
    public $needle;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        do {
            $this->columns[] = $parser->StateFieldPathExpression();
            $parser->match(Lexer::T_COMMA);
        }
        while (!$parser->getLexer()->isNextToken(Lexer::T_INPUT_PARAMETER));

        // Got an input parameter
        $this->needle = $parser->InputParameter();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $haystack = null;

        $first = true;
        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ', ';
            $haystack .= $column->dispatch($sqlWalker);
        }

        return "MATCH(" .
            $haystack .
            ") AGAINST (" .
            $this->needle->dispatch($sqlWalker) .
            " IN BOOLEAN MODE )";
    }
}
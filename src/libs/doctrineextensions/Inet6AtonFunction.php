<?php
/**
 * Inet6AtonFunction.php
 * Project: yaIPAM
 * User: ktammling
 * Date: 27.05.17
 * Time: 15:13
 */

namespace Application\DQL;

use Doctrine\ORM\Query\Lexer;

class Inet6AtonFunction extends \Doctrine\ORM\Query\AST\Functions\FunctionNode
{
    public $valueExpression = null;

    /**
     * parse
     *
     * @param \Doctrine\ORM\Query\Parser $parser
     * @access public
     * @return void
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->valueExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * getSql
     *
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     * @access public
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'INET6_ATON('.$this->valueExpression->dispatch($sqlWalker).')';
    }
}

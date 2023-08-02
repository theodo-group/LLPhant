<?php

namespace LLPhant\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * DateAddFunction ::=
 *     "COSINE_DISTANCE" "(" VectorPrimary "," VectorPrimary ")"
 */

final class PgVectorCosineOperatorDql extends FunctionNode
{
    public $vectorOne;
    public $vectorTwo;

    public function parse(\Doctrine\ORM\Query\Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->vectorOne = $parser->ArithmeticPrimary(); // Fix that, should be vector

        $parser->match(Lexer::T_COMMA);

        $this->vectorTwo = $parser->ArithmeticPrimary(); // Fix that, should be vector

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'COSINE_DISTANCE(' .
            $this->vectorOne->dispatch($sqlWalker) . ', ' .
            $this->vectorTwo->dispatch($sqlWalker) .
        ')';
    }
}

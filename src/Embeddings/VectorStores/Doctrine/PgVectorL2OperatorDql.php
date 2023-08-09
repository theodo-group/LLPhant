<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * L2DistanceFunction ::=
 *     "L2_DISTANCE" "(" VectorPrimary "," VectorPrimary ")"
 */
final class PgVectorL2OperatorDql extends FunctionNode
{
    private Node $vectorOne;

    private Node $vectorTwo;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->vectorOne = $parser->ArithmeticFactor(); // Fix that, should be vector

        $parser->match(Lexer::T_COMMA);

        $this->vectorTwo = $parser->ArithmeticFactor(); // Fix that, should be vector

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'L2_DISTANCE('.
            $this->vectorOne->dispatch($sqlWalker).', '.
            $this->vectorTwo->dispatch($sqlWalker).
        ')';
    }
}

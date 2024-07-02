<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

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
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->vectorOne = $parser->ArithmeticFactor(); // Fix that, should be vector

        $parser->match(TokenType::T_COMMA);

        $this->vectorTwo = $parser->ArithmeticFactor(); // Fix that, should be vector

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'L2_DISTANCE('.
            $this->vectorOne->dispatch($sqlWalker).', '.
            $this->vectorTwo->dispatch($sqlWalker).
        ')';
    }
}

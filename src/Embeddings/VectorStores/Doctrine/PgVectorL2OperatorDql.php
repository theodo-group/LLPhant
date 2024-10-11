<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * L2DistanceFunction ::= "L2_DISTANCE" "(" VectorPrimary "," VectorPrimary ")"
 */
final class PgVectorL2OperatorDql extends FunctionNode
{
    private Node $vectorOne;

    private Node $vectorTwo;

    public function parse(Parser $parser): void
    {
        if (class_exists(\Doctrine\ORM\Query\TokenType::class)) {
            $parser->match(\Doctrine\ORM\Query\TokenType::T_IDENTIFIER);
            $parser->match(\Doctrine\ORM\Query\TokenType::T_OPEN_PARENTHESIS);
        } else {
            $parser->match(\Doctrine\ORM\Query\Lexer::T_IDENTIFIER);
            $parser->match(\Doctrine\ORM\Query\Lexer::T_OPEN_PARENTHESIS);
        }

        $this->vectorOne = $parser->ArithmeticFactor(); // Fix that, should be vector

        if (class_exists(\Doctrine\ORM\Query\TokenType::class)) {
            $parser->match(\Doctrine\ORM\Query\TokenType::T_COMMA);
        } else {
            $parser->match(\Doctrine\ORM\Query\Lexer::T_COMMA);
        }

        $this->vectorTwo = $parser->ArithmeticFactor(); // Fix that, should be vector

        if (class_exists(\Doctrine\ORM\Query\TokenType::class)) {
            $parser->match(\Doctrine\ORM\Query\TokenType::T_CLOSE_PARENTHESIS);
        } else {
            $parser->match(\Doctrine\ORM\Query\Lexer::T_CLOSE_PARENTHESIS);
        }
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'L2_DISTANCE('.
            $this->vectorOne->dispatch($sqlWalker).', '.
            $this->vectorTwo->dispatch($sqlWalker).
            ')';
    }
}

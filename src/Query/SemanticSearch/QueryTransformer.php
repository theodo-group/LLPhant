<?php

namespace LLPhant\Query\SemanticSearch;

interface QueryTransformer
{
    /**
     * @return string[]
     */
    public function transformQuery(string $query): array;
}

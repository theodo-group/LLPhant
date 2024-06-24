<?php

namespace LLPhant\Query\SemanticSearch;

class IdentityTransformer implements QueryTransformer
{
    /**
     * @return string[]
     */
    public function transformQuery(string $query): array
    {
        return [$query];
    }
}

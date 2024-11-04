<?php

namespace LLPhant\Query\SemanticSearch;

class PipeTransformer implements QueryTransformer
{
    /**
     * @var QueryTransformer[]
     */
    private array $transformers;

    public function __construct(QueryTransformer ...$transformers)
    {
        $this->transformers = $transformers;
    }

    public function addTransformer(QueryTransformer $queryTransformer = new IdentityTransformer()): void
    {
        $this->transformers[] = $queryTransformer;
    }

    /**
     * {@inheritDoc}
     */
    public function transformQuery(string $query): array
    {
        $queries = [$query];
        foreach ($this->transformers as $transformer) {
            $newQueries = [];
            foreach ($queries as $query) {
                $newQueries = array_merge($newQueries, $transformer->transformQuery($query));
            }
            $queries = $newQueries;
        }

        return $queries;
    }
}

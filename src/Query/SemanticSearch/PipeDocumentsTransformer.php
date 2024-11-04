<?php

namespace LLPhant\Query\SemanticSearch;

class PipeDocumentsTransformer implements RetrievedDocumentsTransformer
{
    /**
     * @var RetrievedDocumentsTransformer[]
     */
    private array $transformers;

    public function __construct(RetrievedDocumentsTransformer ...$transformers)
    {
        $this->transformers = $transformers;
    }

    public function addTransformer(RetrievedDocumentsTransformer $retrievedDocumentsTransformer = new IdentityDocumentsTransformer()): void
    {
        $this->transformers[] = $retrievedDocumentsTransformer;
    }

    /**
     * {@inheritDoc}
     */
    public function transformDocuments(array $questions, array $retrievedDocs): array
    {
        $docs = $retrievedDocs;

        foreach ($this->transformers as $transformer) {
            $docs = $transformer->transformDocuments($questions, $docs);
        }

        return $docs;
    }
}

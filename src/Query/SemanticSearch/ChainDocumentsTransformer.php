<?php

namespace LLPhant\Query\SemanticSearch;

class ChainDocumentsTransformer implements RetrievedDocumentsTransformer
{
    /** @var RetrievedDocumentsTransformer[] */
    private array $transformers = [];

    public function addTransformer(RetrievedDocumentsTransformer $retrievedDocumentsTransformer = new IdentityDocumentsTransformer()): void
    {
        $this->transformers[] = $retrievedDocumentsTransformer;
    }

    /**
     * {@inheritDoc}
     */
    public function transformDocuments(array $questions, array $retrievedDocs): array
    {
        foreach ($this->transformers as $transformer) {
            $retrievedDocs = $transformer->transformDocuments($questions, $retrievedDocs);
        }

        return $retrievedDocs;
    }
}

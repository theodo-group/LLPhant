<?php

namespace LLPhant\Query\SemanticSearch;

class PipeDocumentsTransformer implements RetrievedDocumentsTransformer
{
    /**
     * @var RetrievedDocumentsTransformer[]
     */
    private readonly array $transformers;

    public function __construct(RetrievedDocumentsTransformer ...$transformers)
    {
        $this->transformers = $transformers;
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

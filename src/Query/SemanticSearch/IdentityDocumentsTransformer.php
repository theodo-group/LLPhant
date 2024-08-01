<?php

namespace LLPhant\Query\SemanticSearch;

class IdentityDocumentsTransformer implements RetrievedDocumentsTransformer
{
    /**
     * {@inheritDoc}
     */
    public function transformDocuments(array $questions, array $retrievedDocs): array
    {
        return $retrievedDocs;
    }
}

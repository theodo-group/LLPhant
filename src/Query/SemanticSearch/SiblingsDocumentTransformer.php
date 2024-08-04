<?php

namespace LLPhant\Query\SemanticSearch;

use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentStore\DocumentStore;

class SiblingsDocumentTransformer implements RetrievedDocumentsTransformer
{
    public function __construct(private readonly DocumentStore $documentStore, private readonly int $nrOfSiblings)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function transformDocuments(array $questions, array $retrievedDocs): array
    {
        /** @var Document[] $extraDocs */
        $extraDocs = [];
        foreach ($retrievedDocs as $retrievedDoc) {
            [$leftIndex, $rightIndex] = $this->getIndices($retrievedDoc->chunkNumber, $this->nrOfSiblings);
            \array_push(
                $extraDocs,
                ...$this->documentStore->fetchDocumentsByChunkRange($retrievedDoc->sourceType, $retrievedDoc->sourceName, $leftIndex, $rightIndex));
        }

        return $extraDocs;
    }

    /**
     * @return int[]
     */
    private function getIndices(int $position, int $numElements): array
    {
        if ($position < 0 || $numElements <= 0) {
            throw new \InvalidArgumentException('Both position and numElements must be positive integers.');
        }

        $halfDistance = intdiv($numElements - 1, 2);
        $halfDistance = min($position, $halfDistance);
        $leftIndex = $position - $halfDistance;
        $rightIndex = $position + ($numElements - 1 - $halfDistance);

        return [$leftIndex, $rightIndex];
    }
}

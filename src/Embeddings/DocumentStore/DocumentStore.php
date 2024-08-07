<?php

namespace LLPhant\Embeddings\DocumentStore;

use LLPhant\Embeddings\Document;

interface DocumentStore
{
    /**
     * @return Document[]
     */
    public function fetchDocumentsByChunkRange(string $sourceType, string $sourceName, int $leftIndex, int $rightIndex): array;
}

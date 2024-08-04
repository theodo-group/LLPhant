<?php

namespace LLPhant\Embeddings\DocumentStore;

use LLPhant\Embeddings\Document;

interface DocumentStore
{
    public function addDocument(Document $document): void;

    /**
     * @param  Document[]  $documents
     */
    public function addDocuments(array $documents): void;

    /**
     * @return iterable<Document>
     */
    public function fetchDocumentsByChunkRange(string $sourceType, string $sourceName, int $leftIndex, int $rightIndex): iterable;
}

<?php

namespace LLPhant\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\Document;

interface EmbeddingGeneratorInterface
{
    /**
     * @return float[]
     */
    public function embedText(string $text, ?int $dimensions = null): array;

    public function embedDocument(Document $document, ?int $dimensions = null): Document;

    /**
     * @param  Document[]  $documents
     * @return Document[]
     */
    public function embedDocuments(array $documents, ?int $dimensions = null): array;

    public function getEmbeddingLength(): int;
}

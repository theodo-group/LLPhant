<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\VectorStores;

use LLPhant\Embeddings\Document;

/**
 * Common parent for all vectorStores.
 */
abstract class VectorStoreBase
{
    abstract public function addDocument(Document $document): void;

    /**
     * @param  Document[]  $documents
     */
    abstract public function addDocuments(array $documents): void;

    /**
     * Return docs most similar to the embedding.
     *
     * @param  float[]  $embedding
     * @param  array<string, string|int>|array<mixed[]>  $additionalArguments
     * @return Document[]
     */
    abstract public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): iterable;
}

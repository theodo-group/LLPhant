<?php

namespace LLPhant\VectorStores;

/**
 * Common parent for all vectorstores.
 */
abstract class VectorStoreBase
{
    /**
     * @param  float[]  $embedding
     */
    abstract public function saveEmbedding(array $embedding, EmbeddingEntityBase $entity): void;

    /**
     * Return docs most similar to the query.
     *
     * @param  array<string, string|int>  $additionalArguments
     * @return mixed[]
     */
    abstract public function similaritySearch(string $query, int $k = 4, array $additionalArguments = []): array;
}

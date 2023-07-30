<?php

namespace LLPhant\VectorStores;

/**
 * Common parent for all vectorstores.
 */
abstract class VectorStoreBase
{
    /**
     * @param array $embedding embedding to save to the vectorstore.
     * @param array $additionalArguments vectorstore specific parameters
     *
     * @return array List of ids from adding the texts into the vectorstore.
     */
    abstract public function saveEmbedding(array $embedding, EmbeddingEntityBase $entity): void;

    /**
     * Return docs most similar to query.
     *
     * @param string $query
     * @param int    $k
     * @param array $additionalArguments vectorstore specific parameters
     *
     * @return array
     */
    abstract public function similaritySearch(string $query, int $k = 4, array $additionalArguments = []): array;
}

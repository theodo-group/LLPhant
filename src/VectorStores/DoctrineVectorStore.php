<?php

namespace LLPhant\VectorStores;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;

class DoctrineVectorStore
{
    private EntityManager $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @param array $embedding
     * @param EmbeddingEntityBase $entity
     * @return void
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveEmbedding(array $embedding, EmbeddingEntityBase $entity): void
    {
        // We need to convert the embedding array to a vector compatible string for postgresql
        $embeddingString = "[" . implode(",", $embedding) . "]";

        $entity->embedding = $embeddingString;
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }


    /**
     * @param string $query
     * @param int $k
     * @param array $additionalArguments
     * @return array
     */
    public function similaritySearch(string $query, int $k = 4, array $additionalArguments = []): array
    {
        return [];
    }
}

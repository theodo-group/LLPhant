<?php

namespace LLPhant\VectorStores;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;

final class DoctrineVectorStore
{
    public function __construct(private readonly \Doctrine\ORM\EntityManager $entityManager)
    {
    }

    /**
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveEmbedding(array $embedding, EmbeddingEntityBase $entity): void
    {
        $entity->embedding = $this->getEmbeddingString($embedding);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * @return EmbeddingEntityBase[]
     *
     * @throws Exception
     * @throws NotSupported
     */
    public function similaritySearch(array $embedding, string $entityClassName = EmbeddingEntityBase::class, int $k = 4, array $additionalArguments = []): array
    {
        // Get the table name from the entity class
        $classMetadata = $this->entityManager->getClassMetadata($entityClassName);
        $tableNameSanitized = $this->sanitize_table_name($classMetadata->getTableName());

        $embeddingString = $this->getEmbeddingString($embedding);

        $sql = "SELECT id FROM {$tableNameSanitized}";
        $whereClauses = [];
        $params = [
            'embeddingString' => $embeddingString,
            'limitCount' => $k,
        ];

        foreach ($additionalArguments as $key => $value) {
            if (! is_scalar($value)) {
                throw new \InvalidArgumentException("Non-scalar value provided for key {$key}");
            }
            // Appending parameter name with a prefix to avoid conflicts with existing parameters
            $paramName = 'where_'.$key;
            $whereClauses[] = $key.' = :'.$paramName;
            // Binding the parameter to its value
            $params[$paramName] = $value;
        }

        if ($whereClauses !== []) {
            $sql .= ' WHERE '.implode(' AND ', $whereClauses);
        }
        $sql .= ' ORDER BY embedding <=> :embeddingString ASC LIMIT :limitCount';
        $resultIds = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();
        /** @var string[]|int[] $ids */
        $ids = array_column($resultIds, 'id');

        $repository = $this->entityManager->getRepository($entityClassName);
        /** @var EmbeddingEntityBase[] $entities */
        $entities = $repository->findBy(['id' => $ids]);

        // We need to sort the entities by the order of the ids from the first query
        $result = [];
        foreach ($ids as $id) {
            $entity = $this->getEntityById($entities, $id);
            if ($entity instanceof \LLPhant\VectorStores\EmbeddingEntityBase) {
                $result[] = $entity;
            }
        }

        return $result;
    }

    /**
     * @param  EmbeddingEntityBase[]  $entities
     */
    private function getEntityById(array $entities, string|int $id): ?EmbeddingEntityBase
    {
        foreach ($entities as $entity) {
            echo $entity->getId()."\n";
            echo $id."\n";
            echo 'plop'."\n";

            if ($entity->getId() === $id) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * We need to convert the embedding array to a vector compatible string for postgresql
     *
     * @param  float[]  $embedding
     */
    private function getEmbeddingString(array $embedding): string
    {
        return '['.implode(',', $embedding).']';
    }

    private function sanitize_table_name(string $table_name): ?string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table_name);
    }
}

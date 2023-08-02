<?php

namespace LLPhant\VectorStores;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use LLPhant\Doctrine\PgVectorCosineOperatorDql;

final class DoctrineVectorStore
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    /**
     * @param  float[]  $embedding
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveEmbedding(array $embedding, EmbeddingEntityBase $entity): void
    {
        $entity->embedding = $this->getEmbeddingString($embedding);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * @param  float[]  $embedding
     *
     * @template T of EmbeddingEntityBase
     *
     * @param  class-string<T>  $entityClassName
     * @param  array<string, string|int>  $additionalArguments
     * @return EmbeddingEntityBase[]
     *
     * @throws Exception
     * @throws NotSupported
     */
    public function similaritySearch(array $embedding, string $entityClassName = EmbeddingEntityBase::class, int $k = 4, array $additionalArguments = []): array
    {
        $this->entityManager->getConfiguration()->addCustomStringFunction('COSINE_DISTANCE', PgVectorCosineOperatorDql::class);

        $repository = $this->entityManager->getRepository($entityClassName);
        $qb = $repository
            ->createQueryBuilder('e')
            ->orderBy('COSINE_DISTANCE(e.embedding, :embeddingString)', 'ASC')
            ->setParameter('embeddingString', $this->getEmbeddingString($embedding))
            ->setMaxResults($k);

        foreach ($additionalArguments as $key => $value) {
            $paramName = 'where_'.$key;
            $qb
                ->andWhere(sprintf('e.%s = :%s', $key, $paramName))
                ->setParameter($paramName, $value);
        }

        return $qb->getQuery()->getResult();
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
}

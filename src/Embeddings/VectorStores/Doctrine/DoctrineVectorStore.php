<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;

final class DoctrineVectorStore extends VectorStoreBase
{
    /**
     * @template T of DoctrineEmbeddingEntityBase
     *
     * @param  class-string<T>  $entityClassName
     *
     * @throws Exception
     */
    public function __construct(private readonly EntityManager $entityManager, public readonly string $entityClassName)
    {
        if (!class_exists(EntityManagerInterface::class)) {
            throw new \RuntimeException("To use this functionality, you must install the `doctrine/orm` package: `composer require doctrine/orm`.");
        }

        new $this->entityClassName();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addDocument(Document $document): void
    {
        $this->persistDocument($document);
        $this->entityManager->flush();
    }

    /**
     * @param  Document[]  $documents
     *
     * @throws \Exception
     */
    public function addDocuments(array $documents): void
    {
        if ($documents === []) {
            return;
        }
        foreach ($documents as $document) {
            $this->persistDocument($document);
        }

        $this->entityManager->flush();
    }

    /**
     * @param  float[]  $embedding The embedding used to search closest neighbors
     * @param  array<string, string|int>  $additionalArguments
     * @return DoctrineEmbeddingEntityBase[]
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        $this->entityManager->getConfiguration()->addCustomStringFunction('L2_DISTANCE', PgVectorL2OperatorDql::class);

        $repository = $this->entityManager->getRepository($this->entityClassName);
        $qb = $repository
            ->createQueryBuilder('e')
            ->orderBy('L2_DISTANCE(e.pgembedding, :embeddingString)', 'ASC')
            ->setParameter('embeddingString', $this->formatEmbeddingForPostgresql($embedding))
            ->setMaxResults($k);

        foreach ($additionalArguments as $key => $value) {
            $paramName = 'where_'.$key;
            $qb
                ->andWhere(sprintf('e.%s = :%s', $key, $paramName))
                ->setParameter($paramName, $value);
        }

        /** @var DoctrineEmbeddingEntityBase[] */
        return $qb->getQuery()->getResult();
    }

    /**
     * We need to convert the embedding array to a vector compatible string for postgresql
     *
     * @param  float[]  $embedding
     */
    private function formatEmbeddingForPostgresql(array $embedding): string
    {
        return '['.implode(',', $embedding).']';
    }

    /**
     * @throws ORMException
     * @throws Exception
     */
    private function persistDocument(Document $document): void
    {
        if (empty($document->embedding)) {
            throw new Exception('Trying to save a document in a vectorStore without embedding');
        }

        $this->entityManager->persist($document);
    }
}

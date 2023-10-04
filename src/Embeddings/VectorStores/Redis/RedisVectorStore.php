<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\VectorStores\Redis;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use Redis;

/**
 * Create JSON + vector store index with
 * ```
 * FT.CREATE "idx_doc"
 *    ON HASH
 *        PREFIX 1 "llphant:"
 *    SCHEMA
 *        "content" TEXT
 *        "type" TEXT
 *        "sourcetype" TEXT
 *        "sourcename" TEXT
 *        "embedding"  VECTOR FLAT
 *            10
 *            "TYPE" "FLOAT32"
 *            "DIM" 512
 *            "DISTANCE_METRIC" "COSINE"
 *            "INITIAL_CAP" 5
 *            "BLOCK_SIZE" 5
 * ```
 */
final class RedisVectorStore extends VectorStoreBase
{
    public const LLPHANT_INDEX_PREFIX = 'llphant:';

    public function __construct(private readonly Redis $redis)
    {
        if (! class_exists(Redis::class)) {
            throw new \RuntimeException('To use this functionality, you must install the `redis` ext: `pecl install redis`.');
        }
    }

    private function generateRedisJsonSetArguments(Document $document): array
    {
        return [
            self::LLPHANT_INDEX_PREFIX . DocumentUtils::getUniqueId($document),
            '$',
            json_encode($document, JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addDocument(Document $document): void
    {
        $this->redis->rawCommand('JSON.SET', ...$this->generateRedisJsonSetArguments($document));
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

        $redisArgs = [];
        foreach ($documents as $document) {
            array_push($redisArgs, ...$this->generateRedisJsonSetArguments($document));
        }

        $this->redis->rawCommand('JSON.MSET', ...$redisArgs);
    }

    /**
     * @param  float[]  $embedding The embedding used to search closest neighbors
     * @param  array<string, string|int>  $additionalArguments
     * @return DoctrineEmbeddingEntityBase[]
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        $searchQuery = "idx_doc '*=>[KNN $k @embedding \$blob AS dist]' SORTBY dist PARAMS 2 content \x01\x01\x01\x01 DIALECT 2";
        return $this->redis->rawCommand('FT.SEARCH', $searchQuery);
    }
}

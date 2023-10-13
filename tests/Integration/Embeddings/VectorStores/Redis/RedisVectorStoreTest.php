<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\VectorStores\Redis;

use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\Redis\RedisVectorStore;

it('tests a full embedding flow with Redis', function () {
    // Get the already embeded france.txt and paris.txt documents
    $path = __DIR__.'/../EmbeddedMock/francetxt_paristxt.json';
    $rawFileContent = file_get_contents($path);
    if (! $rawFileContent) {
        throw new \Exception('File not found');
    }

    $rawDocuments = json_decode($rawFileContent, true);
    $embeddedDocuments = DocumentUtils::createDocumentsFromArray($rawDocuments);

    // Get the embedding of "France the country"
    $path = __DIR__.'/../EmbeddedMock/france_the_country_embedding.json';
    $rawFileContent = file_get_contents($path);
    if (! $rawFileContent) {
        throw new \Exception('File not found');
    }
    /** @var float[] $embeddingQuery */
    $embeddingQuery = json_decode($rawFileContent, true);

    $redisClient = new Predis\Client([
        'scheme' => 'tcp',
        'host' => 'localhost',
        'port' => 6379,
    ]);
    $vectorStore = new RedisVectorStore($redisClient);

    $vectorStore->addDocuments($embeddedDocuments);

    $searchResult1 = $vectorStore->similaritySearch($embeddingQuery, 10);
    expect(getFirstWordOfContentFromResult($searchResult1))->toBe('France');

    $requestParam = [
        'filters' => '@sourceName:paris.txt',
    ];
    $searchResult2 = $vectorStore->similaritySearch($embeddingQuery, 2, $requestParam);
    expect(getFirstWordOfContentFromResult($searchResult2))->toBe('Paris');
});

/**
 * @param  Document[]  $result
 */
function getFirstWordOfContentFromResult(array $result): string
{
    return explode(' ', $result[0]->content)[0];
}

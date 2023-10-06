<?php

declare(strict_types=1);

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Redis\RedisVectorStore;

it('tests a full embedding flow with Redis', function () {
    // [$embeddedDocuments, $embeddingQuery] = getDataFromOpenAi();
    [$embeddedDocuments, $embeddingQuery] = getMockedData();

    $redisClient = new Predis\Client([
        'scheme' => 'tcp',
        'host' => 'localhost',
        'port' => 6379,
    ]);
    $vectorStore = new RedisVectorStore($redisClient);

    $vectorStore->addDocuments($embeddedDocuments);

    $searchResult1 = $vectorStore->similaritySearch($embeddingQuery, 10, []);
    expect(getFirstWordOfContentFromResult($searchResult1))->toBe('France');

    $requestParam = [
        'filters' => '@sourceName:paris.txt',
    ];
    $searchResult2 = $vectorStore->similaritySearch($embeddingQuery, 2, $requestParam);
    expect(getFirstWordOfContentFromResult($searchResult2))->toBe('Paris');
});

function getFirstWordOfContentFromResult(array $result): string
{
    return explode(' ', $result[0]->content)[0];
}

/**
 * @return array{0: Document[], 1: float[]}
 */
function getMockedData(): array
{
    $path = __DIR__.'/../EmbeddedMock/francetxt_paristxt.txt';
    $rawFileContent = file_get_contents($path);
    $rawDocuments = json_decode($rawFileContent, true);

    $embeddedDocuments = DocumentUtils::createDocumentsFromArray($rawDocuments);

    $path = __DIR__.'/../EmbeddedMock/france_the_country_embedding.txt';
    $rawFileContent = file_get_contents($path);
    $embeddingQuery = json_decode($rawFileContent, true);

    return [$embeddedDocuments, $embeddingQuery];
}

/**
 * @return array{0: Document[], 1: float[]}
 */
function getDataFromOpenAi(): array
{
    $filePath = __DIR__.'/../PlacesTextFiles';
    $reader = new FileDataReader($filePath, Document::class);

    $documents = $reader->getDocuments();
    $splittedDocuments = DocumentSplitter::splitDocuments($documents, 200);
    $formattedDocuments = EmbeddingFormatter::formatEmbeddings($splittedDocuments);

    $embeddingGenerator = new OpenAIEmbeddingGenerator();
    $embeddedDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);

    $embeddingGenerator = new OpenAIEmbeddingGenerator();
    $embeddedDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);

    $embeddingQuery = $embeddingGenerator->embedText('France the country');

    return [$embeddedDocuments, $embeddingQuery];
}

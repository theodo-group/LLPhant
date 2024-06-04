<?php

declare(strict_types=1);

use Elastic\Elasticsearch\ClientBuilder;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\Elasticsearch\ElasticsearchVectorStore;

it('tests a full embedding flow with Elasticsearch', function () {
    // Get the already embeded france.txt and paris.txt documents
    $path = __DIR__.'/../EmbeddedMock/francetxt_paristxt.json';
    $rawFileContent = file_get_contents($path);
    if (! $rawFileContent) {
        throw new Exception('File not found');
    }

    $rawDocuments = json_decode($rawFileContent, true);
    $embeddedDocuments = DocumentUtils::createDocumentsFromArray($rawDocuments);

    // Get the embedding of "France the country"
    $path = __DIR__.'/../EmbeddedMock/france_the_country_embedding.json';
    $rawFileContent = file_get_contents($path);
    if (! $rawFileContent) {
        throw new Exception('File not found');
    }
    /** @var float[] $embeddingQuery */
    $embeddingQuery = json_decode($rawFileContent, true);

    $client = (new ClientBuilder())::create()
        ->setHosts([getenv('ELASTIC_URL') ?? 'http://localhost:9200'])
        ->build();
    $vectorStore = new ElasticsearchVectorStore($client, 'llphant_test');

    $vectorStore->addDocuments($embeddedDocuments);

    $searchResult1 = $vectorStore->similaritySearch($embeddingQuery, 2);
    expect(DocumentUtils::getFirstWordFromContent($searchResult1[0]))->toBe('France');

    $requestParam = [
        'filter' => [
            'term' => [
                'sourceName' => 'paris.txt',
            ],
        ],
    ];
    $searchResult2 = $vectorStore->similaritySearch($embeddingQuery, 2, $requestParam);
    expect(DocumentUtils::getFirstWordFromContent($searchResult2[0]))->toBe('Paris');
});

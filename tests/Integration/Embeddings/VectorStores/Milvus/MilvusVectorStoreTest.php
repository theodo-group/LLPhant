<?php

declare(strict_types=1);

use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\Milvus\MilvusClient;
use LLPhant\Embeddings\VectorStores\Milvus\MilvusVectorStore;
use Tests\Fixtures\DocumentFixtures;

describe('MilvusVectorStore', function () {
    beforeEach(function (): void {
        $client = new MilvusClient(getenv('MILVUS_HOST') ?? 'localhost', '19530', 'root', 'milvus');
        /** @var TestCase $this */
        $this->vectorStore = new MilvusVectorStore($client);
    });

    afterEach(function (): void {
        /** @var TestCase $this */
        $this->vectorStore->deleteCollection();
    });

    it('tests a full embedding flow with Milvus', function () {
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

        $this->vectorStore->addDocuments($embeddedDocuments);

        $searchResult1 = $this->vectorStore->similaritySearch($embeddingQuery, 2);
        expect(DocumentUtils::getFirstWordFromContent($searchResult1[0]))->toBe('France');

        $requestParam = [
            'filter' => 'sourceName == "paris.txt"',
        ];
        $searchResult2 = $this->vectorStore->similaritySearch($embeddingQuery, 2, $requestParam);
        expect(DocumentUtils::getFirstWordFromContent($searchResult2[0]))->toBe('Paris');
    });

    it('can fetch documents by chunk range', function () {
        $this->vectorStore->addDocuments([
            DocumentFixtures::documentChunk(1, 'typex', 'namey'),
            DocumentFixtures::documentChunk(0, 'typex', 'namey'),
            DocumentFixtures::documentChunk(3, 'typex', 'namey'),
            DocumentFixtures::documentChunk(2, 'typex', 'namey'),
            DocumentFixtures::documentChunk(4, 'typex', 'namey'),
            DocumentFixtures::documentChunk(0, 'typex', 'namez'),
            DocumentFixtures::documentChunk(1, 'typex', 'namez'),
            DocumentFixtures::documentChunk(2, 'typex', 'namez'),
            DocumentFixtures::documentChunk(0, 'typez', 'namey'),
            DocumentFixtures::documentChunk(1, 'typez', 'namey'),
            DocumentFixtures::documentChunk(2, 'typez', 'namey'),
        ]);

        $range = $this->vectorStore->fetchDocumentsByChunkRange('typex', 'namey', 0, 2);
        expect(\array_map(fn ($d) => DocumentUtils::getUniqueId($d), $range))->toBe(
            [
                DocumentUtils::getUniqueId(DocumentFixtures::documentChunk(0, 'typex', 'namey')),
                DocumentUtils::getUniqueId(DocumentFixtures::documentChunk(1, 'typex', 'namey')),
                DocumentUtils::getUniqueId(DocumentFixtures::documentChunk(2, 'typex', 'namey')),
            ]
        );
    });
});

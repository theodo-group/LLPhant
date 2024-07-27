<?php

namespace Tests\Unit\Chat;

use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\AstraDB\AstraDBVectorStore;
use Tests\Unit\Embeddings\VectorStores\AstraDB\FakeAstraClient;

it('can perform similarity search', function () {
    $fakeClient = FakeAstraClient::astraDBClientWithFakeHttpConnection(file_get_contents(__DIR__.'/astra-find-vector.json'));
    $vectorStore = new AstraDBVectorStore($fakeClient->client);

    $fakeEmbedding = [];
    $similarDocs = $vectorStore->similaritySearch($fakeEmbedding, 2);
    expect(count($similarDocs))->toBe(2)
        ->and($similarDocs[0]->content)->toStartWith('France')
        ->and($similarDocs[1]->content)->toStartWith('The house is on fire')
        ->and(count($similarDocs[0]->embedding))->toBe(1536);
});

it('can insert documents', function () {
    $fakeClient = FakeAstraClient::astraDBClientWithFakeHttpConnection(FakeAstraClient::ASTRA_INSERTED_IDS);

    $vectorStore = new AstraDBVectorStore($fakeClient->client);

    $documents = DocumentUtils::createDocumentsFromArray([
        [
            'content' => 'Hello World!',
            'formattedContent' => 'Hello World!',
            'embedding' => [0.1, 0.3],
            'sourceType' => 'txt',
            'sourceName' => 'hello',
            'hash' => 'abc',
            'chunkNumber' => '0',
        ],
        [
            'content' => 'Ciao mondo!',
            'formattedContent' => 'Ciao mondo!',
            'embedding' => [0.21, 0.34],
            'sourceType' => 'txt',
            'sourceName' => 'hello_it',
            'hash' => 'xyz',
            'chunkNumber' => '0',
        ],
    ]);

    $vectorStore->addDocuments($documents);

    $lastAstraDBRequestBody = $fakeClient->handler->getLastRequest()->getBody()->getContents();
    expect($lastAstraDBRequestBody)->toContain('Hello World!');
});

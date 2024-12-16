<?php

namespace Tests\Unit\Embeddings\VectorStores\TypeSense;

use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\Typesense\TypesenseVectorStore;
use LLPhant\Exception\MissingParameterException;

it('can perform similarity search', function () {
    $fakeClient = FakeTypesenseClientFactory::typesenseClientWithFakeHttpConnection(file_get_contents(__DIR__.'/typesense-find-vector.json'));
    $vectorStore = new TypesenseVectorStore('test_collection', $fakeClient);

    $fakeEmbedding = [];
    $similarDocs = $vectorStore->similaritySearch($fakeEmbedding, 2);
    expect(count($similarDocs))->toBe(2)
        ->and($similarDocs[0]->content)->toBe('Anna lives in Rome')
        ->and($similarDocs[1]->content)->toBe('Anna reads Dante');
});

it('cannot insert documents with no embeddings', function () {
    $fakeClient = FakeTypesenseClientFactory::typesenseClientWithFakeHttpConnection('{}', '{}');
    $vectorStore = new TypesenseVectorStore('test_collection', $fakeClient);

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
            'embedding' => null,
            'sourceType' => 'txt',
            'sourceName' => 'hello_it',
            'hash' => 'xyz',
            'chunkNumber' => '0',
        ],
    ]);

    $vectorStore->addDocuments($documents);
})->throws(MissingParameterException::class);

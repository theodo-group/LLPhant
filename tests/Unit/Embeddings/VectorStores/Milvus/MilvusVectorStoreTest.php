<?php

declare(strict_types=1);

use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\Milvus\MilvusClient;
use LLPhant\Embeddings\VectorStores\Milvus\MilvusVectorStore;
use LLPhant\Exception\SecurityException;

it('can detect wrong sourceType input', function (string $input) {
    $vectorStore = new MilvusVectorStore(Mockery::mock(MilvusClient::class));
    $vectorStore->fetchDocumentsByChunkRange($input, 'aName', 0, 2);
})->with(['"', '%u0022', "'"])->throws(SecurityException::class, 'Invalid source type');

it('can detect wrong sourceName input', function (string $input) {
    $vectorStore = new MilvusVectorStore(Mockery::mock(MilvusClient::class));
    $vectorStore->fetchDocumentsByChunkRange('aType', $input, 0, 2);
})->with(['"', '%u0022', "'"])->throws(SecurityException::class, 'Invalid source name');

it('can call Milvus and decode response the right way', function () {
    $client = Mockery::mock(MilvusClient::class);
    $client->shouldReceive('query')->andReturn([
        'code' => 200,
        'data' => [
            [
                'chunkNumber' => 1,
                'content' => 'Document 1',
                'embedding' => [0.1, 0.2],
                'hash' => 'f08a4cd1811d38fab7ed7ef8763b8fc1',
                'id' => 453153871437234298,
                'sourceName' => 'aName',
                'sourceType' => 'aType',
            ],
        ],
    ]);
    $vectorStore = new MilvusVectorStore($client);
    $documents = $vectorStore->fetchDocumentsByChunkRange('aType', 'aName', 0, 2);
    expect($documents)->toHaveCount(1)
        ->and(DocumentUtils::getUniqueId($documents[0]))->toBe('aType:aName:1');
});

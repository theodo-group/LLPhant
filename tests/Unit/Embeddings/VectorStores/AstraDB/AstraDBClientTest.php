<?php

namespace Tests\Unit\Chat;

use Tests\Unit\Embeddings\VectorStores\AstraDB\FakeAstraClient;

it('can read collection vector size', function () {
    $response = FakeAstraClient::astraDBClientWithFakeHttpConnection(FakeAstraClient::ASTRA_COLLECTION_LIST)->client->collectionVectorDimension();
    expect($response)->toBe(1024);
});

it('can read collection vector size when no collection exists', function () {
    $response = FakeAstraClient::astraDBClientWithFakeHttpConnection(FakeAstraClient::ASTRA_COLLECTION_EMPTY_LIST)->client->collectionVectorDimension();
    expect($response)->toBe(0);
});

it('can perform similarity search', function () {
    $fakeEmbedding = [];
    $response = FakeAstraClient::astraDBClientWithFakeHttpConnection(file_get_contents(__DIR__.'/astra-find-vector.json'))->client->similaritySearch($fakeEmbedding, 2);
    expect($response)->toHaveCount(2)
        ->and($response[0]['content'])->toStartWith('France')
        ->and($response[1]['content'])->toBe('The house is on fire')
        ->and(count($response[0]['embedding']))->toBe(1536);
});

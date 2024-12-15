<?php

namespace Tests\Unit\Embeddings\TypeSense;

use LLPhant\Embeddings\VectorStores\Typesense\TypesenseConfiguration;

it('can generate a Typesense client configuration', function () {
    $configuration = new TypeSenseConfiguration('myKey', ['http://test.com:12345', 'https://www.test.test:8080']);
    $response = $configuration->toArray();
    expect($response)->toBe([
        'api_key' => 'myKey',
        'nodes' => [
            [
                'protocol' => 'http',
                'host' => 'test.com',
                'port' => '12345',
            ],
            [
                'protocol' => 'https',
                'host' => 'www.test.test',
                'port' => '8080',
            ],
        ],
    ]);
});

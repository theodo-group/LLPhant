<?php

namespace Tests\Unit\Embeddings\EmbeddingGenerator\Ollama;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\OllamaConfig;

it('embed a text', function () {
    $config = new OllamaConfig();
    $config->model = 'fake-model';
    $config->url = 'http://fakeurl';
    $generator = new OllamaEmbeddingGenerator($config);

    $mock = new MockHandler([
        new Response(200, [], '{"embedding": [1, 2, 3]}'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // override client for test
    $generator->client = $client;

    expect($generator->embedText('this is the text to embed'))->toBeArray();
});

it('embed a document', function () {
    $config = new OllamaConfig();
    $config->model = 'fake-model';
    $config->url = 'http://fakeurl';
    $generator = new OllamaEmbeddingGenerator($config);

    $mock = new MockHandler([
        new Response(200, [], '{"embedding": [1, 2, 3]}'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // override client for test
    $generator->client = $client;

    $document = new Document();
    $document->formattedContent = 'this is the text to embed';
    expect($generator->embedDocument($document))->toBeInstanceOf(Document::class);
});

it('embed documents', function () {
    $config = new OllamaConfig();
    $config->model = 'fake-model';
    $config->url = 'http://fakeurl';
    $generator = new OllamaEmbeddingGenerator($config);

    $mock = new MockHandler([
        new Response(200, [], '{"embedding": [1, 2, 3]}'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    // override client for test
    $generator->client = $client;

    $document = new Document();
    $document->formattedContent = 'this is the text to embed';

    $result = $generator->embedDocuments([$document]);
    expect($result)->toBeArray();
    expect($result[0])->toBeInstanceOf(Document::class);
});

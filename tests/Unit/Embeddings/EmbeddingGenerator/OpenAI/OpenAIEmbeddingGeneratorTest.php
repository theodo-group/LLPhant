<?php

namespace Tests\Unit\Embedding\EmbeddingGenerator\OpenAI;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\AbstractOpenAIEmbeddingGenerator;
use LLPhant\OpenAIConfig;
use Tests\Fixtures\DocumentFixtures;

it('can handle empty data gracefully', function () {
    $config = new OpenAIConfig();
    $config->apiKey = 'fake-key';

    $generator = new class($config) extends AbstractOpenAIEmbeddingGenerator
    {
        public string $body = '{"object": "list", "model": "test", "usage": {"prompt_tokens": 0, "total_tokens": 0}}';

        public function getEmbeddingLength(): int
        {
            return 1;
        }

        public function getModelName(): string
        {
            return 'test';
        }

        protected function createClientForBatch(): ClientInterface
        {
            $mock = new MockHandler([
                new Response(200, [], $this->body),
            ]);
            $handlerStack = HandlerStack::create($mock);

            return new Client(['handler' => $handlerStack]);
        }
    };

    expect($generator->embedDocuments(DocumentFixtures::documents('Sample document')))->toBeArray();
});

<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator\Mistral;

use Exception;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\AbstractOpenAIEmbeddingGenerator;
use LLPhant\OpenAIConfig;
use OpenAI\Client;

use function getenv;

class MistralEmbeddingGenerator extends AbstractOpenAIEmbeddingGenerator
{
    public Client $client;

    /**
     * @throws Exception
     */
    public function __construct(?OpenAIConfig $config = null)
    {
        if ($config instanceof OpenAIConfig && $config->client instanceof Client) {
            $this->client = $config->client;

            return;
        }

        $apiKey = $config->apiKey ?? getenv('MISTRAL_API_KEY');
        if (! $apiKey) {
            throw new Exception('You have to provide a MISTRAL_API_KEY env var to request Mistral .');
        }

        $this->client = \OpenAI::factory()
            ->withApiKey($apiKey)
            ->withBaseUri('api.mistral.ai/v1')
            ->make();
    }

    public function getEmbeddingLength(): int
    {
        return 1024;
    }

    public function getModelName(): string
    {
        return 'mistral-embed';
    }
}

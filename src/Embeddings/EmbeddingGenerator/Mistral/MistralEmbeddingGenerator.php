<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator\Mistral;

use Exception;
use GuzzleHttp\Client;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\OpenAIConfig;

use function getenv;
use function str_replace;

class MistralEmbeddingGenerator implements EmbeddingGeneratorInterface
{
    public Client $client;

    private readonly string $apiKey;

    /**
     * @throws Exception
     */
    public function __construct(?OpenAIConfig $config = null)
    {
        $apiKey = $config->apiKey ?? getenv('MISTRAL_API_KEY');
        if (! $apiKey) {
            throw new Exception('You have to provide a MISTRAL_API_KEY env var to request Mistral .');
        }
        $this->apiKey = $apiKey;
        $this->client = new Client();
    }

    /**
     * Call out to OpenAI's embedding endpoint.
     *
     * @return float[]
     */
    public function embedText(string $text): array
    {
        $text = str_replace("\n", ' ', $text);

        $response = $this->client->post('https://api.mistral.ai/v1/embeddings', [
            'body' => json_encode([
                'model' => $this->getModelName(),
                'input' => [$text],
            ], JSON_THROW_ON_ERROR),
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        $searchResults = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($searchResults)) {
            throw new Exception("Request to Mistral didn't returned an array: ".$response->getBody()->getContents());
        }

        if (! isset($searchResults['data'][0]['embedding'])) {
            throw new Exception("Request to Mistral didn't returned expected format: ".$response->getBody()->getContents());
        }

        return $searchResults['data'][0]['embedding'];
    }

    public function embedDocument(Document $document): Document
    {
        $text = $document->formattedContent ?? $document->content;
        $document->embedding = $this->embedText($text);

        return $document;
    }

    /**
     * TODO: use the fact that we can send multiple texts to the embedding API
     *
     * @param  Document[]  $documents
     * @return Document[]
     */
    public function embedDocuments(array $documents): array
    {
        $embedDocuments = [];
        foreach ($documents as $document) {
            $embedDocuments[] = $this->embedDocument($document);
        }

        return $embedDocuments;
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

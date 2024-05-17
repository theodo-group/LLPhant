<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator\OpenAI;

use Exception;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\OpenAIConfig;
use OpenAI;
use OpenAI\Client;

use function getenv;
use function str_replace;

abstract class AbstractOpenAIEmbeddingGenerator implements EmbeddingGeneratorInterface
{
    public Client $client;

    /**
     * @throws Exception
     */
    public function __construct(?OpenAIConfig $config = null)
    {
        if ($config instanceof OpenAIConfig && $config->client instanceof Client) {
            $this->client = $config->client;
        } else {
            $apiKey = $config->apiKey ?? getenv('OPENAI_API_KEY');
            if (! $apiKey) {
                throw new Exception('You have to provide a OPENAI_API_KEY env var to request OpenAI .');
            }

            $this->client = OpenAI::client($apiKey);
        }
    }

    /**
     * Call out to OpenAI's embedding endpoint.
     *
     * @return float[]
     *
     * @throws Exception
     */
    public function embedText(string $text, ?int $dimensions = null): array
    {
        if ($dimensions !== null && $this instanceof OpenAIADA002EmbeddingGenerator) {
            throw new Exception('Setting embeddings dimensions is only supported in text-embedding-3 and later models.');
        }

        if ($dimensions !== null && $dimensions > $this->getEmbeddingLength()) {
            throw new Exception(sprintf(
                'The %s model only supports embeddings of length %d or less.',
                $this->getModelName(),
                $this->getEmbeddingLength()
            ));
        }

        $text = str_replace("\n", ' ', $text);

        $response = $this->client->embeddings()->create([
            'model' => $this->getModelName(),
            'input' => $text,
            'dimensions' => $dimensions ?? $this->getEmbeddingLength(),
        ]);

        return $response->embeddings[0]->embedding;
    }

    /**
     * @throws Exception
     */
    public function embedDocument(Document $document, ?int $dimensions = null): Document
    {
        $text = $document->formattedContent ?? $document->content;
        $document->embedding = $this->embedText($text, $dimensions);

        return $document;
    }

    /**
     * @param  Document[]  $documents
     * @return Document[]
     *
     * @throws Exception
     */
    public function embedDocuments(array $documents, ?int $dimensions = null): array
    {
        $embedDocuments = [];
        foreach ($documents as $document) {
            $embedDocuments[] = $this->embedDocument($document, $dimensions);
        }

        return $embedDocuments;
    }

    abstract public function getEmbeddingLength(): int;

    abstract public function getModelName(): string;
}

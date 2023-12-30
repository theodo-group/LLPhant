<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\Document;
use Symfony\Component\HttpClient\HttpClient;

use function str_replace;

/**
  * @author Nicolas Potier <contact@acseo.fr>
  */
final class OllamaEmbeddingGenerator implements EmbeddingGeneratorInterface
{
    public $client;

    public function __construct(string $url, public string $modelName)
    {
        $this->client = HttpClient::create([
            'base_uri' => $url
        ]);
    }

    /**
     * Call out to OpenAI's embedding endpoint.
     *
     * @return float[]
     */
    public function embedText(string $text): array
    {
        $text = str_replace("\n", ' ', $text);

        $response = $this->client->request('POST', '/api/embeddings', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => $this->modelName,
                'prompt' => $text
            ])
        ]);

        return $response->toArray()['embedding'];
    }

    public function embedDocument(Document $document): Document
    {
        $text = $document->formattedContent ?? $document->content;
        $document->embedding = $this->embedText($text);

        return $document;
    }

    /**
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
}

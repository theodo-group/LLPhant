<?php

namespace LLPhant\Embeddings;

use Exception;
use OpenAI;
use OpenAI\Client;

use function getenv;
use function str_replace;

/**
 * Wrapper around OpenAI embedding models.
 */
class OpenAIEmbeddings implements Embeddings
{
    public Client $client;

    public string $modelName = 'text-embedding-ada-002';

    /**
     * OpenAIEmbeddings constructor.
     *
     * Possible config options:
     *   openai_api_key: API key for OpenAI
     *   document_model_name: name of the model used for document embedding
     *   query_model_name: name of the model used for query embedding
     *   embedding_ctx_length: length of the context used for embedding
     *   chunk_size: size of the chunk used for embedding
     *   max_retries: number of retries for embedding
     *
     * @throws Exception
     */
    public function __construct(
    ) {
        $apiKey = getenv('OPENAI_API_KEY');
        if (!$apiKey) {
            throw new Exception('You have to provide a OPENAI_API_KEY env var to request OpenAI .');
        }
        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Call out to OpenAI's embedding endpoint.
     *
     * @param string $text
     *
     * @return array
     */
    public function embedText(string $text): array
    {
        $text = str_replace("\n", ' ', $text);

        $response = $this->client->embeddings()->create([
            'model' => $this->modelName,
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }
}

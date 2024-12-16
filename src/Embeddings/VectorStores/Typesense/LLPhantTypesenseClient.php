<?php

namespace LLPhant\Embeddings\VectorStores\Typesense;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;

class LLPhantTypesenseClient
{
    protected readonly ClientInterface $client;

    public function __construct(
        ?string $node = null,
        ?string $apiKey = null,
        ?ClientInterface $client = null
    ) {
        $this->client = $client instanceof ClientInterface ? $client : $this->createClient($node, $apiKey);
    }

    private function createClient(?string $node, ?string $apiKey): ClientInterface
    {
        if ($node === null) {
            $node = getenv('TYPESENSE_NODE') ?: 'http://localhost:8108';
        }

        if ($apiKey === null) {
            $apiKey = getenv('TYPESENSE_API_KEY') ?: throw new \Exception('You have to provide a TYPESENSE_API_KEY env var to connect to Typesense.');
        }

        return new Client([
            'base_uri' => $node,
            'headers' => [
                'X-TYPESENSE-API-KEY' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function collectionExists(string $name): bool
    {
        try {
            $response = $this->client->request('GET', '/collections/'.$name, []);

            $result = \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return \array_key_exists('name', $result);
        } catch (ServerException|ClientException $e) {
            if ($e->getCode() === 404) {
                return false;
            }
            $response = $e->getResponse();
            throw new \Exception('Typesense API error: '.$response->getBody()->getContents(), $e->getCode(), $e);
        }
    }

    public function createCollection(string $name, int $embeddingLength, string $vectorName): void
    {
        $payload = [
            'name' => $name,
            'fields' => [
                [
                    'name' => $vectorName,
                    'type' => 'float[]',
                    'num_dim' => $embeddingLength,
                ],
                [
                    'name' => 'id',
                    'type' => 'string',
                ],
                [
                    'name' => 'content',
                    'type' => 'string',
                ],
                [
                    'name' => 'hash',
                    'type' => 'string',
                ],
                [
                    'name' => 'sourceName',
                    'type' => 'string',
                ],
                [
                    'name' => 'sourceType',
                    'type' => 'string',
                ],
                [
                    'name' => 'chunkNumber',
                    'type' => 'int32',
                ],
            ],
        ];

        $this->post('/collections', $payload);
    }

    /**
     * @param  array<string, mixed>  $point
     *
     * @throws \JsonException
     */
    public function upsert(string $collectionName, array $point): void
    {
        $this->post('/collections/'.$collectionName.'/documents?action=upsert', $point);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function multiSearch(array $query): array
    {
        return $this->post('/multi_search', $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    private function post(string $path, array $payload): array
    {
        $options = [
            RequestOptions::JSON => $payload,
        ];

        try {
            $response = $this->client->request('POST', $path, $options);

            return \json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (ServerException|ClientException $e) {
            $response = $e->getResponse();
            throw new \Exception('Typesense API error: '.$response->getBody()->getContents(), $e->getCode(), $e);
        }
    }
}

<?php

namespace LLPhant\Query\SemanticSearch;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use LLPhant\Exception\SecurityException;

class LakeraPromptInjectionQueryTransformer implements QueryTransformer
{
    public ClientInterface $client;

    public function __construct(
        ?string $endpoint = 'https://api.lakera.ai/',
        ?string $apiKey = null,
        ?ClientInterface $client = null)
    {
        $this->client = $client instanceof ClientInterface ? $client : $this->createClient($endpoint, $apiKey);
    }

    private function createClient(?string $endpoint, ?string $apiKey): ClientInterface
    {
        if ($endpoint === null) {
            $endpoint = getenv('LAKERA_ENDPOINT') ?: throw new \Exception('You have to provide a LAKERA_ENDPOINT env var to connect to LAKERA.');
        }

        if ($apiKey === null) {
            $apiKey = getenv('LAKERA_API_KEY') ?: throw new \Exception('You have to provide a LAKERA_API_KEY env var to connect to LAKERA.');
        }

        return new Client([
            'base_uri' => $endpoint,
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function transformQuery(string $query): array
    {
        $options = [
            RequestOptions::JSON => [
                'input' => $query,
            ],
        ];

        try {
            $response = $this->client->request('POST', '/v1/prompt_injection', $options);

            $json = $response->getBody()->getContents();

            $responseArray = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            if (array_key_exists('results', $responseArray) && array_key_exists(0, $responseArray['results']) && array_key_exists('flagged', $responseArray['results'][0])) {
                if ($responseArray['results'][0]['flagged'] === true) {
                    throw new SecurityException('Prompt flagged as insecure: '.$query);
                }

                return [$query];
            }

            throw new \Exception('Unexpected response from API: '.$json);
        } catch (ServerException|ClientException $e) {
            $response = $e->getResponse();
            throw new \Exception('Lakera API error: '.$response->getBody()->getContents(), $e->getCode(), $e);
        }
    }
}

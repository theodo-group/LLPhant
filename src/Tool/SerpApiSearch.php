<?php

namespace LLPhant\Tool;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LLPhant\Utils\CLIOutputUtils;

class SerpApiSearch extends ToolBase
{
    private readonly string $apiKey;

    private readonly Client $client;

    /**
     * @throws Exception
     */
    public function __construct(string $apiKey = null, bool $verbose = false)
    {
        parent::__construct($verbose);
        $apiKey ??= getenv('SERP_API_KEY');
        if (! $apiKey) {
            throw new Exception('You have to provide a SERP_API_KEY env var to request SerpApi .');
        }
        $this->apiKey = $apiKey;
        $this->client = new Client(['base_uri' => 'https://serpapi.com/search']);
    }

    /**
     * Perform a Google search using the SerpApi and use OpenAI to extract and generate a clear response.
     *
     * @throws Exception|GuzzleException
     */
    public function search(string $query): string
    {
        $params = ['q' => $query, 'api_key' => $this->apiKey];
        CLIOutputUtils::renderTitleAndMessageOrange('🔧 Executing tool SerpApi', $query, $this->verbose);

        try {
            $response = $this->client->request('GET', '', ['query' => $params]);
            $searchResults = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);

            $results = '';

            if (! is_array($searchResults)) {
                throw new Exception("Request to SerpApi didn't returned values: ".$response->getBody());
            }

            if (isset($searchResults['organic_results']) && is_array($searchResults['organic_results'])) {
                foreach ($searchResults['organic_results'] as $result) {
                    $title = $result['title'] ?? '';
                    $snippet = $result['snippet'] ?? '';
                    $results .= $title.' '.$snippet;
                }
            }

            CLIOutputUtils::render('Results from SerpApi: '.$results, $this->verbose);
            $this->lastResponse = $results;

            return $this->lastResponse;
        } catch (Exception $e) {
            throw new Exception('Request to SerpApi failed: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
}

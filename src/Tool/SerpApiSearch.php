<?php

namespace LLPhant\Tool;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Render\CLIOutputUtils;
use LLPhant\Render\OutputAgentInterface;
use LLPhant\Render\StringParser;

class SerpApiSearch extends ToolBase
{
    private readonly string $apiKey;

    private readonly Client $client;

    /**
     * @throws Exception
     */
    public function __construct(?string $apiKey = null, bool $verbose = false, public OutputAgentInterface $outputAgent = new CLIOutputUtils())
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
     * Perform a Google search and extract a clear response.
     *
     * @throws Exception|GuzzleException
     */
    public function googleSearch(string $googleQuery): string
    {
        $params = ['q' => $googleQuery, 'api_key' => $this->apiKey];
        $this->outputAgent->renderTitleAndMessageOrange('🔧 Executing tool SerpApi', $googleQuery, $this->verbose);

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

            $this->outputAgent->render('Results from SerpApi: '.$results, $this->verbose);
            $this->lastResponse = $results;
            $this->wasSuccessful = true;

            return $this->lastResponse;
        } catch (Exception $e) {
            $this->wasSuccessful = false;
            throw new Exception('Request to SerpApi failed: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Perform a Google Search information using Google.
     *
     * @throws Exception|GuzzleException
     */
    public function searchAndGetPageContent(string $googleQuery, string $informationWeAreLookingFor): string
    {
        $params = ['q' => $googleQuery, 'api_key' => $this->apiKey];
        $this->outputAgent->renderTitleAndMessageOrange('🔧 Executing tool SerpApi', $googleQuery, $this->verbose);

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
                    $link = $result['link'] ?? '';
                    $results .= 'At the URL'.$link.' you can find this information:'.$title.' '.$snippet;
                }
            }

            $gpt = new OpenAIChat();
            $prompt = 'Return ONLY the best URL of the page containing the information about '.$informationWeAreLookingFor.' from this list: '.$results;
            if ($this->verbose) {
                $this->outputAgent->render('Prompt sent to OpenAI: '.$prompt, $this->verbose);
            }

            $gptAnswer = $gpt->generateText('Return the best URL of the page containing the information about '.$informationWeAreLookingFor.' from this list: '.$results);
            $URLs = StringParser::extractURL($gptAnswer);

            if ($URLs === []) {
                $this->wasSuccessful = false;

                return '';
            }

            $this->outputAgent->render('URL found from SerpApi: '.$URLs[0], $this->verbose);

            $this->lastResponse = (new WebPageTextGetter())->getWebPageText($URLs[0]);
            $this->wasSuccessful = true;

            return $this->lastResponse;
        } catch (Exception $e) {
            $this->wasSuccessful = false;
            throw new Exception('Request to SerpApi failed: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
}

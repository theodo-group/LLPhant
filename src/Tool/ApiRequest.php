<?php

namespace LLPhant\Tool;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LLPhant\Utils\CLIOutputUtils;

class ApiRequest extends ToolBase
{
    /**
     * @throws Exception
     */
    public function __construct(bool $verbose = false)
    {
        parent::__construct($verbose);
    }

    /**
     * Perform a http get call to the provided url
     */
    public function get_data_from_url(string $url): string
    {
        try {
            CLIOutputUtils::renderTitleAndMessageOrange('🔧 Executing tool ApiRequest', $url, $this->verbose);
            $client = new Client();
            $response = $client->request('GET', $url);

            $rawContent = $response->getBody()->getContents();
            CLIOutputUtils::render("Results from ApiRequest to {$url}: {$rawContent}", $this->verbose);
            $this->wasSuccessful = true;

        } catch (GuzzleException $e) {
            $this->wasSuccessful = false;
            $this->lastResponse = $e->getMessage();
        }

        return $rawContent ?? '';
    }
}

<?php

namespace LLPhant\Chat;

use Exception;
use LLPhant\Chat\Enums\MistralAIChatModel;
use LLPhant\CloudflareAIConfig;
use LLPhant\OpenAIConfig;
use OpenAI\Client;
use OpenAI\Factory;
use function getenv;

class CloudflareAIChat extends OpenAIChat
{
    private const string BASE_URL = 'https://api.cloudflare.com/client/v4/accounts/';

    /**
     * @throws Exception
     */
    public function __construct(?CloudflareAIConfig $config = null)
    {
        if (! $config instanceof CloudflareAIConfig) {
            $config = new CloudflareAIConfig();
        }

        if (! $config->client instanceof Client) {
            $apiKey = $config->apiKey ?? getenv('CLOUDFLARE_API_KEY');
            if (! $apiKey) {
                throw new Exception('You have to provide a CLOUDFLARE_API_KEY I_KEY env var to request Cloudflare AI.');
            }

            $clientFactory = new Factory();
            $config->client = $clientFactory
                ->withApiKey($apiKey)
                ->withBaseUri(self::BASE_URL)
                ->make();
        }

        $config->model ??= MistralAIChatModel::large->getModelName();
        parent::__construct($config);
    }
}

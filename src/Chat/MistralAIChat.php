<?php

namespace LLPhant\Chat;

use Exception;
use LLPhant\Chat\Enums\MistralAIChatModel;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Exception\MissingFeatureExcetion;
use LLPhant\OpenAIConfig;
use OpenAI\Client;
use OpenAI\Factory;

use function getenv;

class MistralAIChat extends OpenAIChat
{
    private const BASE_URL = 'api.mistral.ai/v1';

    public function __construct(?OpenAIConfig $config = null)
    {
        if (! $config instanceof OpenAIConfig) {
            $config = new OpenAIConfig();
        }

        if (! $config->client instanceof Client) {
            $apiKey = $config->apiKey ?? getenv('MISTRAL_API_KEY');
            if (! $apiKey) {
                throw new Exception('You have to provide a MISTRAL_API_KEY env var to request Mistral AI.');
            }

            $clientFactory = new Factory();
            $config->client = $clientFactory
                ->withApiKey($apiKey)
                ->withBaseUri(self::BASE_URL)
                ->make();
        }

        $config->model ??= MistralAIChatModel::small->getModelName();
        parent::__construct($config);
    }

    /** @param  FunctionInfo[]  $tools */
    public function setTools(array $tools): void
    {
        throw new MissingFeatureExcetion('This feature is not supported');
    }

    public function addTool(FunctionInfo $functionInfo): void
    {
        throw new MissingFeatureExcetion('This feature is not supported');
    }

    /** @param  FunctionInfo[]  $functions */
    public function setFunctions(array $functions): void
    {
        throw new MissingFeatureExcetion('This feature is not supported');
    }

    public function addFunction(FunctionInfo $functionInfo): void
    {
        throw new MissingFeatureExcetion('This feature is not supported');
    }
}

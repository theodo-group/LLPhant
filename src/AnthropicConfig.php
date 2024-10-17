<?php

declare(strict_types=1);

namespace LLPhant;

use GuzzleHttp\Client;
use LLPhant\Chat\Enums\AnthropicChatModel;

/**
 * @phpstan-type ModelOptions array<string, mixed>
 */
class AnthropicConfig
{
    public string $model;

    /**
     * @param  ModelOptions  $modelOptions
     */
    public function __construct(
        public string $url = 'https://api.anthropic.com',
        public string $version = '2023-06-01',
        ?string $model = null,
        public ?string $apiKey = null,
        public int $maxTokens = 1024,
        public array $modelOptions = [],
        public ?Client $client = null,
    ) {
        $this->model = $model ?? AnthropicChatModel::Claude3Haiku->value;
        $this->apiKey ??= (getenv('ANTHROPIC_API_KEY') ?: null);
    }
}

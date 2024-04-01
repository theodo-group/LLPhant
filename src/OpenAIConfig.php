<?php

declare(strict_types=1);

namespace LLPhant;

use OpenAI\Client;

class OpenAIConfig
{
    public string $apiKey;

    public ?Client $client = null;

    public string $model;

    /**
     * model options, example:
     * - temperature
     * - max_tokens
     * - presence_penalty
     * - frequency_penalty
     * - n
     * - logprobs
     * - top_logprobs
     * - stop
     * - user
     * - top_p
     * - response_format
     *
     * @see https://platform.openai.com/docs/api-reference/chat/create
     *
     * @var array<string, mixed>
     */
    public array $modelOptions = [];
}

<?php

declare(strict_types=1);

namespace LLPhant;

use OpenAI\Contracts\ClientContract;

class OpenAIConfig
{
    public string $apiKey;

    public string $url = 'https://api.openai.com/v1';

    public ?ClientContract $client = null;

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

<?php

declare(strict_types=1);

namespace LLPhant;

use OpenAI\Client;

class OpenAIConfig
{
    public string $apiKey;

    public ?Client $client = null;

    public string $model;
}

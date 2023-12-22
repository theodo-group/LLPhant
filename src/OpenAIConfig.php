<?php

namespace LLPhant;

use OpenAI\Client;

class OpenAIConfig
{
    public string $apiKey;

    public ?Client $client = null;

    public string $model;
}

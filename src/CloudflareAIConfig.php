<?php

declare(strict_types=1);

namespace LLPhant;

use OpenAI\Client;

class CloudflareAIConfig
{
    public string $accountID;

    public string $apiKey;

    public ?Client $client = null;

    public string $modelName = "";

}

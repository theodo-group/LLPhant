<?php

namespace LLPhant\Chat;

use LLPhant\Chat\Enums\OpenAIChatModel;

class OpenAIChatConfig
{
    public string $apiKey;
    public OpenAIChatModel| string $model;
}

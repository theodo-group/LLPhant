<?php

declare(strict_types=1);

namespace LLPhant\Chat;

use LLPhant\MistralAIConfig;
use LLPhant\OpenAIConfig;

class MistralAIChat extends OpenAIChat
{
    public function __construct(OpenAIConfig $config = new MistralAIConfig())
    {
        parent::__construct($config);
    }
}

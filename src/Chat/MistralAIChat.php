<?php

declare(strict_types=1);

namespace LLPhant\Chat;

use LLPhant\Exception\MissingParameterException;
use LLPhant\MistralAIConfig;
use LLPhant\OpenAIConfig;

class MistralAIChat extends OpenAIChat
{
    /**
     * @param  MistralAIConfig|null  $config
     *
     * @throws MissingParameterException
     */
    public function __construct(?OpenAIConfig $config = new MistralAIConfig())
    {
        if (! $config instanceof MistralAIConfig) {
            throw new MissingParameterException('config');
        }
        parent::__construct($config);
    }
}

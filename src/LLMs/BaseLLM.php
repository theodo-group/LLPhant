<?php

namespace LLPhant\LLMs;

use OpenAI\Responses\Chat\CreateResponse;

/**
 * Base class for all language models.
 */
abstract class BaseLLM
{
    public function __construct(
    ) {}

    /**
     * @param string $prompt
     * @return CreateResponse
     */
    abstract public function generate(string $prompt): CreateResponse;
}

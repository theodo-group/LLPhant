<?php

namespace LLPhant\Chat;

use OpenAI\Responses\Chat\CreateResponse;

/**
 * Base class for all language models.
 */
abstract class Chat
{
    public function __construct(
    ) {}

    /**
     * @param string $prompt
     * @return CreateResponse
     */
    abstract public function generate(string $prompt): CreateResponse;
}

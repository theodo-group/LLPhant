<?php

namespace LLPhant\Chat;

use OpenAI\Responses\Chat\CreateResponse;

/**
 * Base class for all language models.
 */
abstract class Chat
{
    public function __construct(
    ) {
    }

    abstract public function generate(string $prompt): CreateResponse;
}

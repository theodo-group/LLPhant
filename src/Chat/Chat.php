<?php

namespace LLPhant\Chat;

use OpenAI\Responses\Chat\CreateResponse;

/**
 * Base class for all language models.
 */
abstract class Chat
{
    abstract public function generate(string $prompt): CreateResponse;
}

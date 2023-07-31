<?php

namespace LLPhant\Chat;

/**
 * Base class for all language models.
 */
abstract class Chat
{
    abstract public function generateText(string $prompt): string;
}

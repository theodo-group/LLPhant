<?php

/* Note that CreateStreamedResponse does not contain any usage parameter for now. */

namespace LLPhant\Chat;

use OpenAI\Responses\Chat\CreateResponse;

class TokenUsage
{
    /* Only logs the latest response, not the total amount of all responses */
    public ?int $Prompt_Tokens = 0;

    public ?int $Completion_Tokens = 0;

    public ?int $Total_Tokens = 0;

    public function logLastUsage(?CreateResponse $answer): void
    {
        if (!$answer instanceof CreateResponse) {
            return; // Exit early if $answer is null
        }

        if (isset($answer->usage->promptTokens)) {
            $this->Prompt_Tokens = $answer->usage->promptTokens;
        }
        if (isset($answer->usage->completionTokens)) {
            $this->Completion_Tokens = $answer->usage->completionTokens;
        }
        if (isset($answer->usage->totalTokens)) {
            $this->Total_Tokens = $answer->usage->totalTokens;
        }
    }
}

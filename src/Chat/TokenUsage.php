<?php

/* Note that CreateStreamedResponse does not contain any usage parameter for now. */ 

namespace LLPhant\Chat;

use OpenAI\Responses\Chat\CreateResponse;

class TokenUsage
{
    /* Only logs the latest response, not the total amount of all responses */
    public ?string $Prompt_Tokens = '';
    
    public ?string $Completion_Tokens = '';
    
    public ?string $Total_Tokens = '';

    public function logLastUsage(CreateResponse $answer): void
    {
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

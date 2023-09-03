<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\Function\FunctionBuilder;
use LLPhant\Chat\Function\FunctionInfo;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Tool\SerpApiSearch;

class ExecutionTaskAgent
{
    private readonly SerpApiSearch $searchApi;

    private readonly OpenAIChat $openAIChat;

    /**
     * @param  FunctionInfo[]  $functions
     */
    public function __construct(array $functions, OpenAIChat $openAIChat = null)
    {
        $this->openAIChat = $openAIChat ?? new OpenAIChat();
        $this->searchApi = new SerpApiSearch();
        FunctionBuilder::buildFunctionInfo($this->searchApi, 'search');
        $this->openAIChat->setFunctions($functions);
    }

    public function executeTask(string $objective, Task $task, string $additionalContext = null): void
    {
        if ($additionalContext !== null) {
            $additionalContext = "You can use the following information to help you: {$additionalContext}";
        }

        $prompt = "You are part of a big project. You need to perform the following task: {$task->description}
            {$additionalContext}
            If you have enough information, answer with only the relevant information related to the task.
            Your answer:";

        // Send prompt to OpenAI API and retrieve the result
        $res = '';
        try {
            $res = $this->openAIChat->generateText($prompt);
        } catch (\Exception $e) {
            var_dump('error'.$e->getMessage());
        }

        if ($res === '') {
            $this->executeTask($objective, $task, $this->searchApi->lastResponse);
        } else {
            $task->result = $res;
        }
    }
}

<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\Function\FunctionInfo;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Tool\ToolBase;
use LLPhant\Utils\CLIOutputUtils;

class ExecutionTaskAgent extends AgentBase
{
    private readonly OpenAIChat $openAIChat;

    /**
     * @param  FunctionInfo[]  $functions
     */
    public function __construct(array $functions, OpenAIChat $openAIChat = null, bool $verbose = false)
    {
        parent::__construct($verbose);
        $this->openAIChat = $openAIChat ?? new OpenAIChat();
        $this->openAIChat->setFunctions($functions);
    }

    public function run(string $objective, Task $task, string $additionalContext = null): void
    {
        if ($additionalContext !== null) {
            $additionalContext = "You can use the following information to help you: {$additionalContext}";
        }

        $prompt = "You are part of a big project. You need to perform the following task: {$task->description}
            {$additionalContext}
            If you have enough information, answer with only the relevant information related to the task.
            Your answer:";

        CLIOutputUtils::renderTitleAndMessageGreen('🤖 ExecutionTaskAgent.', 'Prompt: '.$prompt, $this->verbose);

        // Send prompt to OpenAI API and retrieve the result
        $res = '';
        try {
            $res = $this->openAIChat->generateText($prompt);
        } catch (\Exception $e) {
            var_dump('error'.$e->getMessage());
        }

        if ($res === '') {
            // $res === '' means that gpt has chosen to call a function
            // here it means that a tool has been used to gather more information
            $newContext = $additionalContext;
            if (! is_null($this->openAIChat->lastFunctionCalled)) { // should be always true
                $tool = $this->openAIChat->lastFunctionCalled->instance;
                if ($tool instanceof ToolBase) { // this ensures that the function that has been called is a tool
                    $newContext = $additionalContext.' '.$tool->lastResponse;
                }
            }
            $this->run($objective, $task, $newContext);
        } else {
            $task->result = $res;
        }
    }
}

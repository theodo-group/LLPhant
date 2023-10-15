<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\Enums\OpenAIChatModel;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\FunctionRunner;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Utils\CLIOutputUtils;

class ExecutionTaskAgent extends AgentBase
{
    private readonly OpenAIChat $openAIChat;

    private int $iterations = 0;

    /**
     * @param  FunctionInfo[]  $functions
     */
    public function __construct(array $functions, OpenAIChat $openAIChat = null, bool $verbose = false)
    {
        parent::__construct($verbose);
        $this->openAIChat = $openAIChat ?? new OpenAIChat();
        $this->openAIChat->setFunctions($functions);
    }

    public function run(string $objective, Task $task, string $additionalContext = ''): string
    {
        //TODO: add a max length for additionalContext using short term/long term memory
        if ($additionalContext !== '') {
            $additionalContext = "You can use the following information to help you: {$additionalContext}";
        }

        $prompt = "You are part of a big project. You need to perform the following task: {$task->description}
            {$additionalContext}
            If you have enough information, answer with only the relevant information related to the task.
            Your answer:";

        CLIOutputUtils::renderTitleAndMessageGreen('🤖 ExecutionTaskAgent.', 'Prompt: '.$prompt, $this->verbose);

        // Send prompt to OpenAI API and retrieve the result
        try {
            $stringOrFunctionInfo = $this->openAIChat->generateTextOrReturnFunctionCalled($prompt);
            if ($stringOrFunctionInfo instanceof FunctionInfo) {
                // We don't want to agent to try endlessly a task that is not possible to do
                if ($this->iterations >= 5) {
                    return 'Task failed';
                }
                // $toolResponse can be a very long string
                $toolResponse = FunctionRunner::run($stringOrFunctionInfo);
                $refinedData = $this->refineData($objective, $task, $toolResponse);

                $newContext = $additionalContext.$refinedData;
                $this->iterations++;

                return $this->run($objective, $task, $newContext);
            }
            $task->wasSuccessful = true;

            return $stringOrFunctionInfo;
        } catch (\Exception $e) {
            var_dump('error'.$e->getMessage());

            return 'Task failed';
        }
    }

    private function refineData(string $objective, Task $task, string $dataToRefine, int $counter = 0): string
    {
        // Naive approach: if the data is not too long, we don't refine it
        if (strlen($dataToRefine) <= 20000) {
            return $dataToRefine;
        }
        if ($counter >= 3) {
            return $dataToRefine;
        }
        $document = new Document();
        $document->content = $dataToRefine;
        $splittedDocuments = DocumentSplitter::splitDocument($document, 20000);

        $refinedData = '';

        $gpt3 = new OpenAIChat();
        $gpt3->model = OpenAIChatModel::Gpt35Turbo->getModelName();

        foreach ($splittedDocuments as $splittedDocument) {
            //TODO: we should ignore part of the data that is not relevant to the task
            $prompt = "You are part of a big project. The main objective is {$objective}. You need to perform the following task: {$task->description}.
                You MUST be very concise and only extract information that can help for the task and objective.: {$splittedDocument->content}.";
            $refinedData .= $gpt3->generateText($prompt).' ';
        }

        if ($this->verbose) {
            CLIOutputUtils::renderTitleAndMessageOrange('Refined data: ', $refinedData, $this->verbose);
        }

        return $this->refineData($objective, $task, $refinedData, $counter + 1);
    }
}

<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\FunctionRunner;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Experimental\Agent\Render\CLIOutputUtils;
use LLPhant\Experimental\Agent\Render\OutputAgentInterface;

class ExecutionTaskAgent extends AgentBase
{
    public int $refinementIterations = 3;

    // 7000 character is around 4000 tokens,
    // 8000 tokens is the default maximum (input +generation) allowed by OpenAI API per request
    private const MAX_REFINEMENT_REQUEST_LENGTH = 7000;

    /**
     * @param  FunctionInfo[]  $functions
     */
    public function __construct(
        array $functions,
        private readonly OpenAIChat $openAIChat = new OpenAIChat(),
        bool $verbose = false,
        public OutputAgentInterface $outputAgent = new CLIOutputUtils()
    ) {
        parent::__construct($verbose);
        $this->openAIChat->setTools($functions);
    }

    public function run(
        string $objective,
        Task $task,
        string $additionalContext = '',
    ): string {
        $prompt = "You are part of a big project. You need to perform the following task: {$task->description}
            {$additionalContext}
            If you have enough information or if you know that the task has been done, answer with only the relevant information related to the task.
            Your answer:";
        $this->outputAgent->renderTitleAndMessageGreen('🤖 ExecutionTaskAgent.', 'Prompt: '.$prompt, $this->verbose);

        // Send prompt to OpenAI API and retrieve the result
        try {
            $stringOrFunctionInfo = $this->openAIChat->generateTextOrReturnFunctionCalled($prompt);
            if ($stringOrFunctionInfo instanceof FunctionInfo) {
                // $toolResponse can be a very long string
                $toolResponse = FunctionRunner::run($stringOrFunctionInfo);
                $refinedData = is_string($toolResponse) ? $this->refineData($objective, $task,
                    $toolResponse) : 'no data returned';

                $message = "The tool {$stringOrFunctionInfo->name} was used and this is the result:
                    (data from tool) {$refinedData} (end of data from tool)";
                $newContext = $additionalContext.$message;

                $prompt = "You are part of a big project. You are performing the following task: {$task->description}. {$newContext}.
                If you have enough information from using the tool or if you know that the task has been done, answer with only the relevant information related to the task.
                Your answer:";

                $stringOrFunctionInfo = (new OpenAIChat())->generateText($prompt);
            }
            $task->wasSuccessful = true;

            return $stringOrFunctionInfo;
        } catch (\Exception $e) {
            var_dump('error'.$e->getMessage());
            $task->wasSuccessful = false;

            return 'Task failed';
        }
    }

    private function refineData(
        string $objective,
        Task $task,
        ?string $dataToRefine,
        int $counter = 0
    ): string {
        if (is_null($dataToRefine)) {
            return '';
        }

        // Naive approach: if the data is not too long, we don't refine it
        if (strlen($dataToRefine) <= self::MAX_REFINEMENT_REQUEST_LENGTH) {
            return $dataToRefine;
        }
        if ($counter >= $this->refinementIterations) {
            return $dataToRefine;
        }
        $document = new Document();
        $document->content = $dataToRefine;
        $splittedDocuments = DocumentSplitter::splitDocument($document, self::MAX_REFINEMENT_REQUEST_LENGTH);

        $refinedData = '';

        $gpt = new OpenAIChat();

        $splittedDocumentsTotal = count($splittedDocuments);
        $splittedDocumentsCounter = 0;
        foreach ($splittedDocuments as $splittedDocument) {
            $splittedDocumentsCounter++;
            $this->outputAgent->render('📄Refining data: '.$splittedDocumentsCounter.' / '.$splittedDocumentsTotal,
                $this->verbose);
            //TODO: we should ignore part of the data that is not relevant to the task
            $prompt = "You are part of a big project. The main objective is {$objective}. You need to perform the following task: {$task->description}.
                You MUST be very concise and only extract information that can help for the task and objective.
                If you can't find any useful information from the given data, you MUST answer with 'NULL'.
                The data you must use: (start of the data){$splittedDocument->content}(end of the data).";
            $refinedData .= $gpt->generateText($prompt).' ';
        }

        if ($this->verbose) {
            $this->outputAgent->renderTitleAndMessageOrange('Refined data: ', $refinedData, $this->verbose);
        }

        return $this->refineData($objective, $task, $refinedData, $counter + 1);
    }
}

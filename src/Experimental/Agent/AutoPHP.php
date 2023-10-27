<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Utils\CLIOutputUtils;

use function Termwind\terminal;

class AutoPHP
{
    public OpenAIChat $openAIChat;

    public TaskManager $taskManager;

    public ExecutionTaskAgent $executionAgent;

    public CreationTaskAgent $creationTaskAgent;

    public PrioritizationTaskAgent $prioritizationTaskAgent;

    /**
     * @param  FunctionInfo[]  $functionsAvailable
     */
    public function __construct(
        public string $objective,
        /* @var FunctionInfo[] */
        public array $functionsAvailable,
        public bool $verbose = false,
        public int $refinementIteration = 3
    ) {
        $this->taskManager = new TaskManager();
        $this->openAIChat = new OpenAIChat();
        $this->creationTaskAgent = new CreationTaskAgent($this->taskManager, null, $verbose);
        $this->prioritizationTaskAgent = new PrioritizationTaskAgent($this->taskManager, null, $verbose);
    }

    public function run(int $maxIteration = 100): string
    {
        terminal()->clear();
        CLIOutputUtils::renderTitle('🐘 AutoPHP 🐘', '🎯 Objective: '.$this->objective, $this->verbose);
        $this->creationTaskAgent->createTasks($this->objective);
        CLIOutputUtils::printTasks($this->verbose, $this->taskManager->tasks);
        $currentTask = $this->prioritizationTaskAgent->prioritizeTask($this->objective);
        $iteration = 1;
        while ($currentTask instanceof Task && $maxIteration >= $iteration) {
            CLIOutputUtils::printTasks($this->verbose, $this->taskManager->tasks, $currentTask);

            // TODO: add a mechanism to retrieve short-term / long-term memory
            $context = $this->prepareNeededDataForTaskCompletion($currentTask);

            // TODO: add a mechanism to get the best tool for a given Task

            $executionAgent = new ExecutionTaskAgent($this->functionsAvailable, null, $this->refinementIteration,
                $this->verbose);
            $currentTask->result = $executionAgent->run($this->objective, $currentTask, $context);

            CLIOutputUtils::printTasks($this->verbose, $this->taskManager->tasks);
            if ($finalResult = $this->getObjectiveResult()) {
                CLIOutputUtils::renderTitle('🏆️ Success! 🏆️', 'Result: '.$finalResult, true);

                return $finalResult;
            }

            if (count($this->taskManager->getUnachievedTasks()) <= 1) {
                $this->creationTaskAgent->createTasks($this->objective);
            }

            $currentTask = $this->prioritizationTaskAgent->prioritizeTask($this->objective);
            $iteration++;
        }

        return "failed to achieve objective in {$iteration} iterations";
    }

    private function prepareNeededDataForTaskCompletion(Task $task): string
    {
        if ($this->taskManager->getAchievedTasks() === []) {
            return '';
        }

        $previousCompletedTask = $this->taskManager->getAchievedTasksNameAndResult();
        $prompt = "You are a data analyst expert.
        You need to select the data needed to perform the following task from previous tasks: {$task->description}
        Previous tasks: {$previousCompletedTask}";

        if ($this->verbose) {
            CLIOutputUtils::render('Prepare data from previous tasks. Prompt:'.$prompt, $this->verbose);
        }

        return $this->openAIChat->generateText($prompt);
    }

    private function getObjectiveResult(): ?string
    {
        $achievedTasks = $this->taskManager->getAchievedTasksNameAndResult();

        $prompt = "Consider the ultimate objective of your team: {$this->objective}."
            .'Based on the data from previous tasks, you need to determine if the objective has been achieved.'
            ."The previous tasks are: {$achievedTasks}."
            ."If you have enough data, give the exact answer to the objective {$this->objective}. If you don't have enought data, simply return 'no'.";

        $response = $this->openAIChat->generateText($prompt);

        if (strtolower($response) !== 'no') {
            return $response;
        }

        return null;
    }
}

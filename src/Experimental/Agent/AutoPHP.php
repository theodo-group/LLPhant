<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\Function\FunctionInfo;
use LLPhant\Chat\OpenAIChat;

class AutoPHP
{
    public OpenAIChat $openAIChat;

    public TaskManager $taskManager;

    public ExecutionTaskAgent $executionAgent;

    public CreationTaskAgent $creationTaskAgent;

    public PrioritizationTaskAgent $prioritizationTaskAgent;

    /**
     * @param  FunctionInfo[]  $functions
     */
    public function __construct(public string $objective, array $functions)
    {
        $this->taskManager = new TaskManager();
        $this->openAIChat = new OpenAIChat();
        $this->executionAgent = new ExecutionTaskAgent($functions);
        $this->creationTaskAgent = new CreationTaskAgent($this->taskManager);
        $this->prioritizationTaskAgent = new PrioritizationTaskAgent($this->taskManager);
    }

    public function run(int $maxIteration = 100): string
    {
        $this->creationTaskAgent->createTasks($this->objective);
        $currentTask = $this->prioritizationTaskAgent->prioritizeTask($this->objective);
        $iteration = 1;
        while ($currentTask instanceof Task && $maxIteration >= $iteration) {
            var_dump($this->taskManager->tasks);
            $context = $this->prepareNeededDataForTaskCompletion($currentTask);
            $this->executionAgent->executeTask($this->objective, $currentTask, $context);

            if ($finalResult = $this->getObjectiveResult()) {
                return $finalResult;
            }

            if ($this->taskManager->getUnachievedTasks() === []) {
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

        return $this->openAIChat->generateText($prompt);
    }

    private function getObjectiveResult(): ?string
    {
        $achievedTasks = $this->taskManager->getAchievedTasksNameAndResult();

        $prompt = "Consider the ultimate objective of your team: {$this->objective}."
            .'Based on the data from previous tasks, you need to determine if the objective has been achieved.'
            ."The previous tasks are: {$achievedTasks}."
            ."If you have enough data, give the answer to the objective {$this->objective}. If not, simply return 'no'.";

        $response = $this->openAIChat->generateText($prompt);

        if (strtolower($response) !== 'no') {
            return $response;
        }

        return null;
    }
}

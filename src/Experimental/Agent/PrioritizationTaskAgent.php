<?php

namespace LLPhant\Experimental\Agent;

use LLPhant\Chat\OpenAIChat;

class PrioritizationTaskAgent
{
    private readonly OpenAIChat $openAIChat;

    public function __construct(private readonly TaskManager $taskManager, OpenAIChat $openAIChat = null)
    {
        $this->openAIChat = $openAIChat ?? new OpenAIChat();
    }

    public function prioritizeTask($objective): ?Task
    {
        $unachievedTasks = '';
        foreach ($this->taskManager->getUnachievedTasks() as $key => $task) {
            $unachievedTasks .= "id:{$key} name: {$task->name}.";
        }
        $achievedTasks = $this->taskManager->getAchievedTasksNameAndResult();

        // Prepare the prompt using the provided information
        $prompt = "Consider the ultimate objective of your team: {$objective}.
                You are a task prioritization AI tasked with reprioritizing the following tasks: {$unachievedTasks}."
            ." To help you the previous tasks are: {$achievedTasks}."
            .' Return the id of the next task that will bring us closer to achieve the objective';

        $response = $this->openAIChat->generateText($prompt);

        // Look for the first number in the response
        if (preg_match('/\d+/', $response, $matches)) {
            $firstNumber = $matches[0];
            if (isset($this->taskManager->getUnachievedTasks()[$firstNumber])) {

                return $this->taskManager->getUnachievedTasks()[$firstNumber];
            }
        }

        return $this->taskManager->getNextTask();
    }
}

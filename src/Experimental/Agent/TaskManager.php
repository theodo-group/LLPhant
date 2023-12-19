<?php

namespace LLPhant\Experimental\Agent;

class TaskManager
{
    /** @var Task[] */
    public array $tasks = [];

    /**
     * @param  mixed[]  $tasks
     */
    public function addTasks(array $tasks): void
    {

        $tasksObject = [];
        foreach ($tasks as $task) {
            if (! is_array($task)) {
                continue;
            }
            $tasksObject[] = new Task($task['name'], $task['description']);
        }

        $this->tasks = array_merge($this->tasks, $tasksObject);
    }

    public function getNextTask(): ?Task
    {
        foreach ($this->tasks as $task) {
            if ($task->result === null) {
                return $task;
            }
        }

        return null;
    }

    /**
     * @return Task[]
     */
    public function getAchievedTasks(): array
    {
        $achievedTasks = [];
        foreach ($this->tasks as $task) {
            if ($task->result !== null) {
                $achievedTasks[] = $task;
            }
        }

        return $achievedTasks;
    }

    /**
     * @return Task[]
     */
    public function getUnachievedTasks(): array
    {
        $unachievedTasks = [];
        foreach ($this->tasks as $task) {
            if ($task->result === null) {
                $unachievedTasks[] = $task;
            }
        }

        return $unachievedTasks;
    }

    public function getAchievedTasksNameAndResult(): string
    {
        $previousCompletedTask = '';
        foreach ($this->getAchievedTasks() as $task) {
            $previousCompletedTask .= "Task: {$task->name}. Result: {$task->result} \n";
        }

        return $previousCompletedTask;
    }

    public function getUnachievedTasksNameAndResult(): string
    {
        $unachievedTasks = '';
        foreach ($this->getUnachievedTasks() as $task) {
            $unachievedTasks .= "Task: {$task->name}.";
        }

        return $unachievedTasks;
    }
}

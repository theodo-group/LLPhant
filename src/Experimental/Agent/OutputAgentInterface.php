<?php

namespace LLPhant\Experimental\Agent;

interface OutputAgentInterface
{
    public function render(string $message, bool $verbose): void;

    public function renderTitle(string $title, string $message, bool $verbose): void;

    public function renderTitleAndMessageGreen(string $title, string $message, bool $verbose): void;

    public function renderTitleAndMessageOrange(string $title, string $message, bool $verbose): void;

    /**
     * @param  Task[]  $tasks
     */
    public function printTasks(bool $verbose, array $tasks, ?Task $currentTask = null): void;
}

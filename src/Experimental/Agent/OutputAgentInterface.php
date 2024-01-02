<?php

namespace LLPhant\Experimental\Agent;

interface OutputAgentInterface
{
    public static function render(string $message, bool $verbose): void;

    public static function renderTitle(string $title, string $message, bool $verbose): void;

    public static function renderTitleAndMessageGreen(string $title, string $message, bool $verbose): void;

    public static function renderTitleAndMessageOrange(string $title, string $message, bool $verbose): void;

    /**
     * @param  Task[]  $tasks
     */
    public static function printTasks(bool $verbose, array $tasks, ?Task $currentTask = null): void;
}

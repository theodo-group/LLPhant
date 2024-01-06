<?php

namespace LLPhant\Experimental\Agent\Render;

use LLPhant\Experimental\Agent\Task;

use function Termwind\{render};

class CLIOutputUtils implements OutputAgentInterface
{
    public function render(string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message);
        render($message);
    }

    public function renderTitle(string $title, string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message);

        render('<div><div class="px-1 bg-blue-500">'.$title.'</div><em class="ml-1">'.$message.'</em></div>');
    }

    public function renderTitleAndMessageGreen(string $title, string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message, $title);
        render('<div><div class="px-1 bg-green-300">'.$title.'</div><em class="ml-1">'.$message.'</em></div>');
    }

    public function renderTitleAndMessageOrange(string $title, string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message, $title);
        render('<div><div class="px-1 bg-orange-300">'.$title.'</div><em class="ml-1">'.$message.'</em></div>');
    }

    public function renderResult(string $result): void
    {
        $this->renderTitle('🏆️ Success! 🏆️ Result:', $result, true);
    }

    /**
     * @param  Task[]  $tasks
     */
    public function printTasks(bool $verbose, array $tasks, ?Task $currentTask = null): void
    {
        $liItems = '';
        foreach ($tasks as $task) {
            if ($currentTask === $task) {
                $liItems .= "<li class='font-bold text-pink-400'>⚙️ - {$task->name} ({$task->description})</li>";

                continue;
            }

            if (is_null($task->result)) {
                $liItems .= "<li class='font-bold text-pink-400'>⚪️ - {$task->name} ({$task->description})</li>";

                continue;
            }

            $result = self::truncateString($verbose, $task->result, $task->name);

            if ($task->wasSuccessful) {
                $liItems .= "<li class='font-bold text-pink-400'>🟢 - {$task->name} ({$task->description}) - {$result}</li>";
            } else {
                $liItems .= "<li class='font-bold text-pink-400'>🔴 - {$task->name} ({$task->description})</li>";
            }
        }

        render('<div class="px-4 mt-2 mb-2">
                        <h1 class="font-bold">List of tasks:</h1>
                        <ul class="list-disc">'.$liItems.'</ul>
                    </div>'
        );
    }

    private static function truncateString(bool $verbose, string $message, ?string $title = null): string
    {
        $maxSize = 250;
        if ($title) {
            $maxSize -= strlen($title);
        }

        if (! $verbose) {
            $message = str_replace('\n', '', $message);
            $message = str_replace('\r', '', $message);
            if (strlen($message) > $maxSize) {
                $message = substr($message, 0, $maxSize).'...';
            }
        }

        return $message;
    }
}

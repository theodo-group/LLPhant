<?php

namespace LLPhant\Render;

class CLIOutputUtils implements OutputAgentInterface
{
    public function render(string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message);
        echo $message.PHP_EOL;
    }

    public function renderTitle(string $title, string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message);

        $separator = \str_repeat('*', 80);

        $this->render($separator, $verbose);
        $this->render($title.' *** '.$message.' ***', $verbose);
        $this->render($separator, $verbose);
    }

    public function renderTitleAndMessageGreen(string $title, string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message, $title);
        $this->renderTitle('🍏 '.$title, $message, $verbose);
    }

    public function renderTitleAndMessageOrange(string $title, string $message, bool $verbose): void
    {
        $message = self::truncateString($verbose, $message, $title);
        $this->renderTitle('🔸 '.$title, $message, $verbose);
    }

    public function renderResult(string $result): void
    {
        $this->renderTitle('🏆️ Success! 🏆️ Result:', $result, true);
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

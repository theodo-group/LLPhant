<?php

namespace LLPhant\Render;

interface OutputAgentInterface
{
    public function render(string $message, bool $verbose): void;

    public function renderTitle(string $title, string $message, bool $verbose): void;

    public function renderTitleAndMessageGreen(string $title, string $message, bool $verbose): void;

    public function renderTitleAndMessageOrange(string $title, string $message, bool $verbose): void;

    public function renderResult(string $result): void;
}

<?php

declare(strict_types=1);

namespace LLPhant;

class OllamaConfig
{
    public string $model;

    public string $url = 'http://localhost:11434/api/';

    public bool $stream = false;

    public bool $formatJson = false;
}

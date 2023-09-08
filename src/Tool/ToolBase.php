<?php

namespace LLPhant\Tool;

abstract class ToolBase
{
    public string $lastResponse = '';

    public function __construct(public bool $verbose)
    {
    }
}

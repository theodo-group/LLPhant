<?php

namespace LLPhant\Tool;

abstract class ToolBase
{
    public string $lastResponse = '';

    public bool $wasSuccessful;

    public function __construct(public bool $verbose)
    {
    }
}

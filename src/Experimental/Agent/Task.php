<?php

namespace LLPhant\Experimental\Agent;

class Task
{
    public bool $wasSuccessful = false;

    public bool $doing = false;

    public function __construct(public string $name, public string $description, public ?string $result = null)
    {
    }
}

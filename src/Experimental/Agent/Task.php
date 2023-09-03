<?php

namespace LLPhant\Experimental\Agent;

class Task
{
    public function __construct(public string $name, public string $description, public ?string $result = null)
    {
    }
}

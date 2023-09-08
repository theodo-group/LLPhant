<?php

namespace LLPhant\Experimental\Agent;

abstract class AgentBase
{
    public function __construct(protected bool $verbose = true)
    {
    }
}

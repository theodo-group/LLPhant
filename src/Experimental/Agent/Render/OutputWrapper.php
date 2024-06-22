<?php

namespace LLPhant\Experimental\Agent\Render;

class OutputWrapper
{
    public function __construct(public mixed $content, public string $type)
    {
    }
}

<?php

namespace LLPhant\Experimental\Agent\Web;

class OutputWrapper
{
    public function __construct(public mixed $content, public string $type)
    {
    }
}

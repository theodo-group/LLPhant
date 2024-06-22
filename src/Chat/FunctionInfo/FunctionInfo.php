<?php

namespace LLPhant\Chat\FunctionInfo;

class FunctionInfo
{
    public string $jsonArgs;

    /**
     * @param  Parameter[]  $parameters
     * @param  Parameter[]  $requiredParameters
     */
    public function __construct(public string $name, public mixed $instance, public string $description, public array $parameters, public array $requiredParameters = [])
    {
    }
}

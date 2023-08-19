<?php

namespace LLPhant\Chat\Function;

class FunctionInfo
{
    /**
     * @param  Parameter[]  $parameters
     * @param  Parameter[]  $requiredParameters
     */
    public function __construct(public string $name, public string $className, public string $description, public array $parameters, public array $requiredParameters = [])
    {
        //TODO add check that it is a name from properties
    }
}

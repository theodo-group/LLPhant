<?php

namespace LLPhant\Chat\Function;

class FunctionInfo {
    public string $name;
    public string $className;

    public string $description;
    /** @var Parameter[]  */
    public array $parameters;

    /** @var Parameter[]  */
    public array $requiredParameters;

    /**
     * @param string $name
     * @param string $className
     * @param string $description
     * @param Parameter[] $parameters
     * @param Parameter[] $requiredParameters
     */
    public function __construct(string $name, string $className, string $description, array $parameters, array $requiredParameters = []) {
        $this->name = $name;
        $this->className = $className;
        $this->description = $description;
        $this->parameters = $parameters;
        $this->requiredParameters = $requiredParameters; //TODO add check that it is a name from properties
    }
}

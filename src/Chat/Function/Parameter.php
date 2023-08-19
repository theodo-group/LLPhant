<?php

namespace LLPhant\Chat\Function;

class Parameter
{
    /**
     * @param  mixed[]  $enum
     */
    public function __construct(public string $name, public string $type, public string $description, public array $enum = [], public ?string $format = null)
    {
    }
}

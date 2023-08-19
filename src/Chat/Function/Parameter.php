<?php

namespace LLPhant\Chat\Function;

class Parameter {
    public string $name;
    public string $type;
    public string $description;
    /** @var mixed[]  */
    public ?array $enum;
    public ?string $format;

    /**
     * @param string $type
     * @param string $description
     * @param mixed[] $enum
     * @param string $format
     */
    public function __construct(string $name, string $type, string $description, array $enum = [], $format = null) {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->enum = $enum;
        $this->format = $format;
    }
}

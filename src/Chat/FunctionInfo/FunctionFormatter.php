<?php

namespace LLPhant\Chat\FunctionInfo;

class FunctionFormatter
{
    /**
     * @deprecated Switch to using tools instead of functions in your code
     *
     * @param  FunctionInfo[]  $functions
     * @return mixed[]
     */
    public static function formatFunctionsToOpenAI(array $functions): array
    {
        if ($functions === []) {
            return [];
        }

        $functionsOpenAI = [];
        foreach ($functions as $function) {
            $functionsOpenAI[] = self::formatOneFunctionToOpenAI($function);
        }

        return $functionsOpenAI;
    }

    /**
     * @deprecated Switch to using tools instead of functions in your code
     *
     * @return array{name: string, description: string, parameters: array{type: string, properties: array<string, mixed[]>, required: string[]}}
     */
    public static function formatOneFunctionToOpenAI(FunctionInfo $functionInfo): array
    {
        $parametersOpenAI = [];
        foreach ($functionInfo->parameters as $parameter) {
            $param = self::formatParameter($parameter);
            $parametersOpenAI[$parameter->name] = $param;
        }

        $requiredParametersOpenAI = [];
        foreach ($functionInfo->requiredParameters as $requiredParameter) {
            $requiredParametersOpenAI[] = $requiredParameter->name;
        }

        return [
            'name' => $functionInfo->name,
            'description' => $functionInfo->description,
            'parameters' => [
                'type' => 'object',
                'properties' => $parametersOpenAI,
                'required' => $requiredParametersOpenAI,
            ],
        ];
    }

    /**
     * @return array{type: string, description: string, items?: array{type: string, properties?: array<string, array{type: string, description: string}>}, properties?: array<string, array{type: string, description: string}>, enum?: mixed[], format?: string}
     *
     * @throws \Exception
     */
    public static function formatParameter(Parameter $parameter): array
    {
        $param = [
            'type' => $parameter->type,
            'description' => $parameter->description,
        ];

        if ($parameter->type === 'array') {
            if ($parameter->itemsOrProperties === null) {
                throw new \Exception('Array type parameter must have items description. Define a type or use the Parameter class for object.');
            }

            if (is_string($parameter->itemsOrProperties)) {
                $param['items'] = [
                    'type' => $parameter->itemsOrProperties,
                ];
            } else {
                $properties = [];
                /** @var Parameter $property */
                foreach ($parameter->itemsOrProperties as $property) {
                    $properties[$property->name] = [
                        'type' => $property->type,
                        'description' => $property->description,
                    ];
                }

                $param['items'] = [
                    'type' => 'object',
                    'properties' => $properties,
                ];
            }
        }

        if ($parameter->type === 'object') {
            if (! is_array($parameter->itemsOrProperties)) {
                throw new \Exception('Object type parameter must have properties description. You need to pass an array of Parameter.');
            }

            $properties = [];
            /** @var Parameter $item */
            foreach ($parameter->itemsOrProperties as $item) {
                $properties[$item->name] = [
                    'type' => $item->type,
                    'description' => $item->description,
                ];
            }

            $param['properties'] = $properties;
        }

        if ($parameter->enum) {
            $param['enum'] = $parameter->enum;
        }

        if ($parameter->format) {
            $param['format'] = $parameter->format;
        }

        return $param;
    }
}

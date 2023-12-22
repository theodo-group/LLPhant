<?php

namespace LLPhant\Chat\FunctionInfo;

class ToolFormatter
{
    public static function formatFunctionsToOpenAITools(array $functions): array
    {
        if ($functions === []) {
            return [];
        }

        $toolsOpenAI = [];
        foreach ($functions as $function) {
            $toolsOpenAI[] = self::formatOneToolToOpenAI($function);
        }

        return $toolsOpenAI;
    }

    /**
     * @return array{type: string, function: array{name: string, description: string, parameters: array{type: string, properties: array<string, mixed[]>, required: string[]}}}
     */
    public static function formatOneToolToOpenAI(FunctionInfo $functionInfo): array
    {
        $parametersOpenAI = [];
        foreach ($functionInfo->parameters as $parameter) {
            $param = FunctionFormatter::formatParameter($parameter);
            $parametersOpenAI[$parameter->name] = $param;
        }

        $requiredParametersOpenAI = [];
        foreach ($functionInfo->requiredParameters as $requiredParameter) {
            $requiredParametersOpenAI[] = $requiredParameter->name;
        }

        return [
            'type' => 'function',
            'function' => [
                'name' => $functionInfo->name,
                'description' => $functionInfo->description,
                'parameters' => [
                    'type' => 'object',
                    'properties' => $parametersOpenAI,
                    'required' => $requiredParametersOpenAI,
                ],
            ],
        ];
    }
}

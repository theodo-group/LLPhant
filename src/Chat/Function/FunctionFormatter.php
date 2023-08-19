<?php

namespace LLPhant\Chat\Function;

class FunctionFormatter
{
    /**
     * @return array{name: string, description: string, parameters: array{type: string, properties: array<string, array{type: string, description: string, enum?: mixed[], format?: string}>, required: string[]}}
     */
    public static function formatToOpenAI(FunctionInfo $functionInfo): array
    {
        $parametersOpenAI = [];
        foreach ($functionInfo->parameters as $parameter) {
            $param = [
                'type' => $parameter->type,
                'description' => $parameter->description,
            ];

            if ($parameter->enum) {
                $param['enum'] = $parameter->enum;
            }

            if ($parameter->format) {
                $param['format'] = $parameter->format;
            }

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
}

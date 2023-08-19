<?php

namespace LLPhant\Chat\Function;

use ReflectionMethod;
use ReflectionNamedType;

class FunctionBuilder
{
    public static function buildFunctionInfo(object $instance, string $name): FunctionInfo
    {
        $reflection = new ReflectionMethod($instance::class, $name);
        $params = $reflection->getParameters();

        $parameters = [];
        $requiredParameters = [];

        foreach ($params as $param) {
            /** @var ReflectionNamedType $reflectionType */
            $reflectionType = $param->getType();

            $newParameter = new Parameter($param->getName(), TypeMapper::mapPhpTypeToJsonSchemaType($reflectionType), '');
            $parameters[] = $newParameter;
            if (! $param->isOptional()) {
                $requiredParameters[] = $newParameter;
            }
        }

        $docComment = $reflection->getDocComment() ?: '';
        // Remove PHPDoc annotations and get only the description
        $functionDescription = preg_replace('/\s*\* @.*/', '', $docComment);
        $functionDescription = trim(str_replace(['/**', '*/', '*'], '', $functionDescription ?? ''));

        return new FunctionInfo($name, $instance, $functionDescription, $parameters, $requiredParameters);
    }
}

<?php

use LLPhant\Chat\Function\FunctionFormatter;
use LLPhant\Chat\Function\FunctionInfo;
use LLPhant\Chat\Function\Parameter;

it('can format function info to OpenAI format', function () {
    $parameters = [
        new Parameter('param1', 'string', 'description1'),
        new Parameter('param2', 'integer', 'description2', ['enum1', 'enum2'], 'format1'),
    ];

    $requiredParameters = [
        new Parameter('param1', 'string', 'description1'),
    ];

    $functionInfo = new FunctionInfo('testFunction', 'TestClass', 'testDescription', $parameters, $requiredParameters);

    $expected = [
        'name' => 'testFunction',
        'description' => 'testDescription',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'param1' => [
                    'type' => 'string',
                    'description' => 'description1',
                ],
                'param2' => [
                    'type' => 'integer',
                    'description' => 'description2',
                    'enum' => ['enum1', 'enum2'],
                    'format' => 'format1',
                ],
            ],
            'required' => ['param1'],
        ],
    ];

    expect(FunctionFormatter::formatOneFunctionToOpenAI($functionInfo))->toBe($expected);
});

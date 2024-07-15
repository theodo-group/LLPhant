<?php

namespace Tests\Unit\Chat;

use LLPhant\Chat\Anthropic\AnthropicMessage;

it('generates a correct tool result message for Anthropic', function () {

    $expectedJson = <<<'JSON'
    {
        "role": "user",
        "content": [
            {
                "type": "tool_result",
                "tool_use_id": "toolu_01A09q90qw90lq917835lq9",
                "content": "15 degrees"
            }
        ]
    }
    JSON;

    $toolsOutput = ['toolu_01A09q90qw90lq917835lq9' => '15 degrees'];

    expect(\json_encode(AnthropicMessage::toolResultMessage($toolsOutput), JSON_PRETTY_PRINT))->toBe($expectedJson);
});

it('generates a correct assistant answer message for Anthropic', function () {

    $expectedJson = <<<'JSON'
    {
        "role": "assistant",
        "content": [
            {
                "type": "text",
                "text": "<thinking>I need to call the get_weather function, and the user wants SF, which is likely San Francisco, CA.<\/thinking>"
            },
            {
                "type": "tool_use",
                "id": "toolu_01A09q90qw90lq917835lq9",
                "name": "get_weather",
                "input": {
                    "location": "San Francisco, CA",
                    "unit": "celsius"
                }
            }
        ]
    }
    JSON;

    $assistantAnswer = [
        ['type' => 'text', 'text' => '<thinking>I need to call the get_weather function, and the user wants SF, which is likely San Francisco, CA.</thinking>'],
        ['type' => 'tool_use', 'id' => 'toolu_01A09q90qw90lq917835lq9', 'name' => 'get_weather', 'input' => ['location' => 'San Francisco, CA', 'unit' => 'celsius']],
    ];

    expect(\json_encode(AnthropicMessage::fromAssistantAnswer($assistantAnswer), JSON_PRETTY_PRINT))->toBe($expectedJson);
});

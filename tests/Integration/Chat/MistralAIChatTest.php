<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

use LLPhant\Chat\MistralAIChat;
use LLPhant\OpenAIConfig;

it('can generate some stuff', function () {
    $chat = new MistralAIChat();
    $response = $chat->generateText('what is one + one ?');
    expect($response)->toBeString();
});

it('can generate some stuff with a system prompt', function () {
    $chat = new MistralAIChat();
    $chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
    $response = $chat->generateText('what is one + one ?');
    expect(strtolower($response))->toBe('ok');
});

it('can load any existing model', function () {
    $config = new OpenAIConfig();
    $config->model = 'mistral-tiny';
    $chat = new MistralAIChat($config);
    $response = $chat->generateText('one + one ?');
    expect($response)->toBeString();
});

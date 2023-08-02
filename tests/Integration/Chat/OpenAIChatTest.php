<?php

use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;

it('can generate some stuff', function () {
    $chat = new OpenAIChat();
    $response = $chat->generateText('what is one + one ?');
    expect($response)->toBeString();
});

it('can generate some stuff with a system prompt', function () {
    $chat = new OpenAIChat();
    $chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
    $response = $chat->generateText('what is one + one ?');
    expect($response)->toBe('ok');
});

it('can load any existing model', function () {
    $config = new OpenAIConfig();
    $config->model = 'gpt-3.5-turbo-16k';
    $chat = new OpenAIChat($config);
    $response = $chat->generateText('one + one ?');
    expect($response)->toBeString();
});

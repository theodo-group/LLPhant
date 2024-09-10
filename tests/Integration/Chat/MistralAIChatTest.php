<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

use LLPhant\Chat\MistralAIChat;
use LLPhant\MistralAIConfig;
use OpenAI\Client;

it('can be supplied with a custom client', function () {
    $client = \Mockery::mock(Client::class);
    $client->shouldReceive('chat')->once();

    $config = new MistralAIConfig();
    $config->client = $client;

    $chat = new MistralAIChat($config);
    $chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
    $response = $chat->generateText('what is one + one ?');
    expect($response)->toBeString();
});

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
    $config = new MistralAIConfig();
    $config->model = 'mistral-tiny';
    $chat = new MistralAIChat($config);
    $response = $chat->generateText('one + one ?');
    expect($response)->toBeString();
});

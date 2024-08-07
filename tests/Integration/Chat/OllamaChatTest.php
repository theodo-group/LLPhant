<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

use LLPhant\Chat\OllamaChat;
use LLPhant\OllamaConfig;

function ollamaChat(): OllamaChat
{
    $config = new OllamaConfig();
    $config->model = 'llama3';
    $config->url = getenv('OLLAMA_URL') ?: 'http://localhost:11434/api/';

    return new OllamaChat($config);
}

it('can generate some stuff', function () {
    $chat = ollamaChat();
    $response = $chat->generateText('what is 1 + 1?');
    expect($response)->toBeString()->and($response)->toContain('2');
});

it('can generate some stuff with a system prompt', function () {
    $chat = ollamaChat();
    $chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
    $response = $chat->generateText('what is one + one?');
    expect(strtolower($response))->toStartWith('ok');
});

it('can generate some stuff using a stream', function () {
    $chat = ollamaChat();
    $response = $chat->generateStreamOfText('Can you describe the recipe for making carbonara in 5 steps');
    expect($response->__toString())->toContain('eggs');
});

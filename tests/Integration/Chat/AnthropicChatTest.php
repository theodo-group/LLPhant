<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

use LLPhant\Chat\AnthropicChat;

it('can generate some stuff', function () {
    $chat = new AnthropicChat();
    $response = $chat->generateText('what is one + one?');
    expect($response)->toBeString()->and($response)->toContain('two');
});

it('can generate some stuff with a system prompt', function () {
    $chat = new AnthropicChat();
    $chat->setSystemMessage('Whatever we ask you, you MUST answer "ok"');
    $response = $chat->generateText('what is one + one?');
    expect(strtolower($response))->toStartWith('ok');
});

it('can generate some stuff using a stream', function () {
    $chat = new AnthropicChat();
    $response = $chat->generateStreamOfText('Can you describe the recipe for making carbonara in 5 steps');
    expect($response->__toString())->toContain('eggs');
});

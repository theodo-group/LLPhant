<?php

namespace Tests\E2E;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Chat\OpenAIChatConfig;

it('can generate some stuff', function () {
    $chat = new OpenAIChat();
    $response = $chat->generate('what is one + one ?');

    expect($response->choices[0]->message->content)->toBeString();
});

it('can load any existing model', function () {
    $config = new OpenAIChatConfig();
    $config->model = 'gpt-3.5-turbo-16k';
    $chat = new OpenAIChat($config);
    $response = $chat->generate('one + one ?');
    expect($response->choices[0]->message->content)->toBeString();
});

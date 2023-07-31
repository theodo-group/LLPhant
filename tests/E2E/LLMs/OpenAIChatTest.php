<?php

namespace Tests\E2E;

use LLPhant\Chat\OpenAIChat;

it('can generate some stuff', function () {
    $chat = new OpenAIChat();
    $response = $chat->generate('what is one + one ?');

    expect($response->choices[0]->message->content)->toBeString();
});

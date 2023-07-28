<?php

use LLPhant\LLMs\OpenAIChat;

it('can generate some shit', function () {
    $chat = new OpenAIChat();

    $response = $chat->generate("what is one + one ?");

    echo $response->choices[0]->message->content;

    expect($response->choices[0]->message->content)->toBeString();
});

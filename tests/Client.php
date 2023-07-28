<?php

use LLPhant\Embeddings\OpenAIEmbeddings;
use LLPhant\LLMs\OpenAIChat;

it('can generate some stuff', function () {
    $chat = new OpenAIChat();

    $response = $chat->generate("what is one + one ?");

    echo $response->choices[0]->message->content;

    expect($response->choices[0]->message->content)->toBeString();
});

it('can embed some stuff', function () {
    $llm = new OpenAIEmbeddings();

    $response = $llm->embedText("I love food");

    print_r($response);

    expect($response)->toBeArray();
});

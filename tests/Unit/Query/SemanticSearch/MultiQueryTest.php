<?php

use LLPhant\Chat\ChatInterface;
use LLPhant\Query\SemanticSearch\MultiQuery;

it('Returns an array whose first line is the original query', function () {
    $answer = "First line\nSecond line\nThird line";
    $chat = Mockery::mock(ChatInterface::class);
    $chat->allows([
        'setSystemMessage' => null,
        'generateText' => $answer,
    ]);

    $multiQuery = new MultiQuery($chat);

    expect($multiQuery->transformQuery('Original query'))
        ->toBe(['Original query', 'First line', 'Second line', 'Third line']);
});

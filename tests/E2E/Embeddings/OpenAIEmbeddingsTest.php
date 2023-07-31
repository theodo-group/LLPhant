<?php

use LLPhant\Embeddings\OpenAIEmbeddings;

it('can embed some stuff', function () {
    $llm = new OpenAIEmbeddings();
    $response = $llm->embedText('I love food');
    expect($response[0])->toBeFloat();
});

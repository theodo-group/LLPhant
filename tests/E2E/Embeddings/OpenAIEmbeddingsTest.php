<?php

use LLPhant\Embeddings\OpenAIEmbeddings;

it('can embed some stuff', function () {
    $llm = new OpenAIEmbeddings();
    $embedding = $llm->embedText('I love food');
    expect($embedding[0])->toBeFloat();
});

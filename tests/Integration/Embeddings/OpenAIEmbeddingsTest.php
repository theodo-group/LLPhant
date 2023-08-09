<?php

use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;

it('can embed some stuff', function () {
    $llm = new OpenAIEmbeddingGenerator();
    $embedding = $llm->embedText('I love food');
    expect($embedding[0])->toBeFloat();
});

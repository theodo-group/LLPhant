<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAIADA002EmbeddingGenerator;

it('can embed some stuff', function () {
    $llm = new OpenAIADA002EmbeddingGenerator();
    $embedding = $llm->embedText('I love food');
    expect($embedding[0])->toBeFloat();
});

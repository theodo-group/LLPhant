<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\EmbeddingGenerator\Mistral\MistralEmbeddingGenerator;

it('can embed some stuff', function () {
    $llm = new MistralEmbeddingGenerator();
    $embedding = $llm->embedText('I love food');
    expect($embedding[0])->toBeFloat();
});

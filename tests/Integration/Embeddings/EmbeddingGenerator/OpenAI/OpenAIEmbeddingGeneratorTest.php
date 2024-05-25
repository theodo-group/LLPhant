<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3LargeEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAIADA002EmbeddingGenerator;

it('can embed some stuff', function () {
    $llms = [
        new OpenAIADA002EmbeddingGenerator(),
        new OpenAI3LargeEmbeddingGenerator(),
        new OpenAI3SmallEmbeddingGenerator(),
    ];

    foreach ($llms as $llm) {
        $embedding = $llm->embedText('I love food');
        expect($embedding[0])->toBeFloat();
    }
});

it('can embed with custom dimensions', function () {
    $llms = [
        new OpenAI3LargeEmbeddingGenerator(),
        new OpenAI3SmallEmbeddingGenerator(),
    ];

    foreach ($llms as $llm) {
        $embedding = $llm->embedText('I love food', 512);
        expect(count($embedding))->toBe(512);
    }
});

it('throws an exception when trying to set dimensions on a model that does not support it', function () {
    $llm = new OpenAIADA002EmbeddingGenerator();
    $llm->embedText('I love food', 512);
})->fails('Setting embeddings dimensions is only supported in text-embedding-3 and later models.');

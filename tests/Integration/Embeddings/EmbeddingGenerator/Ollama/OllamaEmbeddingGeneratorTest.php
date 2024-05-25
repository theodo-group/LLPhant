<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\OllamaConfig;

it('should not allow setting dimensions', function () {
    $llm = new OllamaEmbeddingGenerator(new OllamaConfig());
    $llm->embedText('I love food', 10);
})->fails('Setting embeddings dimensions is not supported.');

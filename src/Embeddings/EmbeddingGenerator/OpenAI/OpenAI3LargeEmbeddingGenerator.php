<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator\OpenAI;

final class OpenAI3LargeEmbeddingGenerator extends AbstractOpenAIEmbeddingGenerator
{
    public function getEmbeddingLength(): int
    {
        return 3072;
    }

    public function getModelName(): string
    {
        return 'text-embedding-3-large';
    }
}

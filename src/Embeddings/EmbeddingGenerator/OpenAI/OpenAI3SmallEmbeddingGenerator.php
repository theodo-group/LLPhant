<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator\OpenAI;

final class OpenAI3SmallEmbeddingGenerator extends AbstractOpenAIEmbeddingGenerator
{
    public function getEmbeddingLength(): int
    {
        return 1536;
    }

    public function getModelName(): string
    {
        return 'text-embedding-3-small';
    }
}

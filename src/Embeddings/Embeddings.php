<?php

declare(strict_types=1);

namespace LLPhant\Embeddings;

interface Embeddings
{
    /**
     * @return float[]
     */
    public function embedText(string $text): array;

    /**
     * @return float[]
     */
    public function embedDocument(Document $document): array;
}

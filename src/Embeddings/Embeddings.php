<?php

namespace LLPhant\Embeddings;

interface Embeddings
{
    /**
     * @return float[]
     */
    public function embedText(string $text): array;
}

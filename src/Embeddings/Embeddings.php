<?php

namespace LLPhant\Embeddings;

interface Embeddings
{
    public function embedText(string $text): array;
}

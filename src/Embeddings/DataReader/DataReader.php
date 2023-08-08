<?php

namespace LLPhant\Embeddings\DataReader;

interface DataReader
{
    /**
     * @return Document[]
     */
    public function getDocuments(): array;
}

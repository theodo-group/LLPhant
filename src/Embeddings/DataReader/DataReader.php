<?php

namespace LLPhant\Embeddings\DataReader;

use LLPhant\Embeddings\Document;

interface DataReader
{
    /**
     * @return Document[]
     */
    public function getDocuments(): array;
}

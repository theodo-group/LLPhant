<?php

namespace LLPhant\Embeddings;

class DocumentUtils
{
    public static function getUniqueId(Document $document): string
    {
        return $document->sourceType.':'.$document->sourceName.':'.$document->chunkNumber;
    }
}

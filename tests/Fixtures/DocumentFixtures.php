<?php

namespace Tests\Fixtures;

use LLPhant\Embeddings\Document;

class DocumentFixtures
{
    private function __construct()
    {
    }

    public static function documentChunk(int $i, string $sourceType, string $sourceName): Document
    {
        $document = new Document();
        $document->sourceName = $sourceName;
        $document->sourceType = $sourceType;
        $document->chunkNumber = $i;
        $document->content = 'Document '.$i;
        $document->hash = \md5($document->content);
        // Fake embedding
        $document->embedding = [0.1, 0.2];

        return $document;
    }
}

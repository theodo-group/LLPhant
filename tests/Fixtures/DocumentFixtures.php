<?php

namespace Tests\Fixtures;

use LLPhant\Embeddings\Document;

class DocumentFixtures
{
    /**
     * @return array<Document>
     */
    public static function documents(string ...$contents): array
    {
        $result = [];
        foreach ($contents as $content) {
            $newDocument = new Document();
            $newDocument->content = $content;
            $result[] = $newDocument;
        }

        return $result;
    }

    public static function documentChunk(int $i, string $sourceType, string $sourceName): Document
    {
        $document = new Document();
        $document->sourceName = $sourceName;
        $document->sourceType = $sourceType;
        $document->chunkNumber = $i;
        $document->content = 'Document '.$i;
        $document->hash = \md5($document->content);

        return $document;
    }
}

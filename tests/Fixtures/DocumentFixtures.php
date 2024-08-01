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
}

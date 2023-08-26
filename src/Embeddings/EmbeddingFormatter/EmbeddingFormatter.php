<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingFormatter;

use LLPhant\Embeddings\Document;

final class EmbeddingFormatter
{
    /**
     *  You can pass here a lot of valuable information in the header like
     *  the author of the document, the date of the document, etc.
     */
    public static function formatEmbedding(Document $document, string $header = ''): Document
    {
        $header .= "The name of the source is: {$document->sourceName}.";

        $document->formattedContent = $header.$document->content;

        return $document;
    }

    /**
     * @param  Document[]  $documents
     * @return Document[]
     */
    public static function formatEmbeddings(array $documents, string $header = ''): array
    {
        $formattedDocuments = [];
        foreach ($documents as $document) {
            $formattedDocuments[] = self::formatEmbedding($document, $header);
        }

        return $formattedDocuments;
    }
}

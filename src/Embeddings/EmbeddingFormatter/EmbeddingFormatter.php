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
    public static function formatEmbedding(Document $document, string $header): Document
    {
        $header .= $document->sourceName !== null
            ? "The name of the source is: {$document->sourceName}."
            : '';

        $document->formattedContent = $header.$document->content;

        return $document;
    }
}

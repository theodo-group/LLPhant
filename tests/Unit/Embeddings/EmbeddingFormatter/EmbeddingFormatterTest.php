<?php

declare(strict_types=1);

namespace Tests\Unit\Embeddings\EmbeddingFormatter;

use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;

it('format a content from a document by adding a header in the formatted content', function () {
    $document = new Document();
    $document->content = 'This is the content';
    $document->sourceName = 'source';
    $documentFormatted = EmbeddingFormatter::formatEmbedding($document, 'This is a header.');
    expect($documentFormatted->formattedContent)->toBe('This is a header.The name of the source is: source.This is the content');
});

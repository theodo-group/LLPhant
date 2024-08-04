<?php

declare(strict_types=1);

namespace Tests\Unit\Query\SemanticSearch;

use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentStore\DocumentStore;
use LLPhant\Query\SemanticSearch\SiblingsDocumentTransformer;
use Tests\Fixtures\DocumentFixtures;

function documentStore(): DocumentStore
{
    return new class implements DocumentStore
    {
        public function fetchDocumentsByChunkRange(string $sourceType, string $sourceName, int $leftIndex, int $rightIndex): array
        {
            /** @var Document[] $documents */
            $documents = [];
            for ($i = $leftIndex; $i <= $rightIndex; $i++) {
                $documents[] = DocumentFixtures::documentChunk($i, $sourceType, $sourceName);
            }

            return $documents;
        }

        public function addDocument(Document $document): void
        {
            // Unused
        }

        public function addDocuments(array $documents): void
        {
            // Unused
        }
    };
}

it('can extract right data from document store', function () {
    $documents = [DocumentFixtures::documentChunk(7, 'txt', 'test')];
    $transformer = new SiblingsDocumentTransformer(documentStore(), 20);
    $transformedDocuments = $transformer->transformDocuments(['Sample question'], $documents);
    expect(count($transformedDocuments))->toBe(20)
        ->and($transformedDocuments[0]->chunkNumber)->toBe(0)
        ->and($transformedDocuments[19]->chunkNumber)->toBe(19);
});

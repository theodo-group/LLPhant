<?php

namespace Tests\Unit\Chat;

use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\Memory\MemoryVectorStore;
use Tests\Fixtures\DocumentFixtures;

it('can fetch documents by chunk range', function () {
    $memoryVectorStore = new MemoryVectorStore();
    $memoryVectorStore->addDocuments([
        DocumentFixtures::documentChunk(1, 'typex', 'namey'),
        DocumentFixtures::documentChunk(0, 'typex', 'namey'),
        DocumentFixtures::documentChunk(3, 'typex', 'namey'),
        DocumentFixtures::documentChunk(2, 'typex', 'namey'),
        DocumentFixtures::documentChunk(4, 'typex', 'namey'),
        DocumentFixtures::documentChunk(0, 'typex', 'namez'),
        DocumentFixtures::documentChunk(1, 'typex', 'namez'),
        DocumentFixtures::documentChunk(2, 'typex', 'namez'),
        DocumentFixtures::documentChunk(0, 'typez', 'namey'),
        DocumentFixtures::documentChunk(1, 'typez', 'namey'),
        DocumentFixtures::documentChunk(2, 'typez', 'namey'),
    ]);

    $range = $memoryVectorStore->fetchDocumentsByChunkRange('typex', 'namey', 0, 2);
    expect(\array_map(fn ($d) => DocumentUtils::getUniqueId($d), $range))->toBe(
        [
            DocumentUtils::getUniqueId(DocumentFixtures::documentChunk(0, 'typex', 'namey')),
            DocumentUtils::getUniqueId(DocumentFixtures::documentChunk(1, 'typex', 'namey')),
            DocumentUtils::getUniqueId(DocumentFixtures::documentChunk(2, 'typex', 'namey')),
        ]
    );
});

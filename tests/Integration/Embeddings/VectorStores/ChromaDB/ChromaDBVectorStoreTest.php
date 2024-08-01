<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\VectorStores\ChromaDB;

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3LargeEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\ChromaDB\ChromaDBVectorStore;
use Tests\Fixtures\DocumentFixtures;
use Tests\Integration\Embeddings\VectorStores\Doctrine\PlaceEntity;

it('creates two documents with their embeddings and perform a similarity search', function () {

    $vectorStore = new ChromaDBVectorStore(getenv('CHROMADB_HOST') ?: 'localhost');

    $embeddingGenerator = new OpenAI3LargeEmbeddingGenerator();

    $docs = DocumentFixtures::documents(
        'Anna reads Dante',
        'I love carbonara',
        'Do not put pineapples on pizza',
        'New York is in the USA',
        'My cat is black',
        'Anna lives in Rome'
    );

    $embeddedDocuments = $embeddingGenerator->embedDocuments($docs);

    $vectorStore->addDocuments($embeddedDocuments);

    $embedding = $embeddingGenerator->embedText('Anna lives in Italy');
    $result = $vectorStore->similaritySearch($embedding, 2);

    // We check that the search returns the correct documents in the right order
    expect($result[0]->content)->toBe('Anna lives in Rome');
});

it('tests a full embedding flow with ChromaDB', function () {
    $filePath = __DIR__.'/../PlacesTextFiles';
    $reader = new FileDataReader($filePath, PlaceEntity::class);
    $documents = $reader->getDocuments();
    $splittedDocuments = DocumentSplitter::splitDocuments($documents, 100, "\n");
    $formattedDocuments = EmbeddingFormatter::formatEmbeddings($splittedDocuments);

    $embeddingGenerator = new OpenAI3LargeEmbeddingGenerator();
    $embeddedDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);

    $vectorStore = new ChromaDBVectorStore(getenv('CHROMADB_HOST') ?: 'localhost');
    $vectorStore->addDocuments($embeddedDocuments);

    $embedding = $embeddingGenerator->embedText('France the country');
    /** @var PlaceEntity[] $result */
    $result = $vectorStore->similaritySearch($embedding, 2);

    // We check that the search return the correct entities in the right order
    expect(explode(' ', $result[0]->content)[0])->toBe('France');
});

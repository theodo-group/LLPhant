<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\VectorStores\Typesense;

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3LargeEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Typesense\TypesenseVectorStore;
use Tests\Integration\Embeddings\VectorStores\Doctrine\PlaceEntity;

it('can create a brand new collection and does not fail if it tries to recreate it', function () {
    $collectionName = 'test_collection'.\random_int(PHP_INT_MIN, PHP_INT_MAX);
    $vectorStore = new TypesenseVectorStore($collectionName);

    expect($vectorStore->collectionExists($collectionName))->toBe(false);

    $vectorStore->createCollectionIfDoesNotExist($collectionName, 1024);
    expect($vectorStore->collectionExists($collectionName))->toBe(true);

    $vectorStore->createCollectionIfDoesNotExist($collectionName, 1024);
    expect($vectorStore->collectionExists($collectionName))->toBe(true);
});

it('creates two documents with their embeddings and perform a similarity search', function () {

    $vectorStore = new TypesenseVectorStore('test_collection');

    $embeddingGenerator = new OpenAI3LargeEmbeddingGenerator();
    $vectorStore->createCollectionIfDoesNotExist('test_collection', $embeddingGenerator->getEmbeddingLength());

    $docs = DocumentUtils::documents(
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

it('tests a full embedding flow with Typesense', function () {
    $filePath = __DIR__.'/../PlacesTextFiles';
    $reader = new FileDataReader($filePath, PlaceEntity::class);
    $documents = $reader->getDocuments();
    $splittedDocuments = DocumentSplitter::splitDocuments($documents, 100, "\n");
    $formattedDocuments = EmbeddingFormatter::formatEmbeddings($splittedDocuments);

    $embeddingGenerator = new OpenAI3LargeEmbeddingGenerator();
    $embeddedDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);

    $vectorStore = new TypesenseVectorStore('test_collection');

    $vectorStore->addDocuments($embeddedDocuments);

    $embedding = $embeddingGenerator->embedText('France the country');
    /** @var PlaceEntity[] $result */
    $result = $vectorStore->similaritySearch($embedding, 2);

    // We check that the search return the correct entities in the right order
    expect(explode(' ', $result[0]->content)[0])->toBe('France');
});

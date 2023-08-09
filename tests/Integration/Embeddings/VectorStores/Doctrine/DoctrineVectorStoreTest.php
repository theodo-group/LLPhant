<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use LLPhant\Embeddings\DataReader\TextFileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineVectorStore;
use Tests\Integration\Embeddings\VectorStores\Doctrine\PlaceEntity;

it('creates two entity with their embeddings and perform a similarity search', function () {
    $config = ORMSetup::createAttributeMetadataConfiguration(
        [__DIR__.'/src'],
        true
    );

    $connectionParams = [
        'dbname' => 'postgres',
        'user' => 'myuser',
        'password' => '!ChangeMe!',
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
    ];

    $connection = DriverManager::getConnection($connectionParams);
    $connection->executeQuery('TRUNCATE TABLE test_place');
    $entityManager = new EntityManager($connection, $config);

    $vectorStore = new DoctrineVectorStore($entityManager, PlaceEntity::class);
    $embeddingGenerator = new OpenAIEmbeddingGenerator();

    $paris = new PlaceEntity();
    $paris->content = 'I live in Paris';
    $paris->type = 'city';
    $france = new PlaceEntity();
    $france->content = 'I live in France';
    $france->type = 'country';

    $embededDocuments = $embeddingGenerator->embedDocuments([$paris, $france]);

    $vectorStore->addDocuments($embededDocuments);

    $embedding = $embeddingGenerator->embedText('I live in Asia');
    /** @var PlaceEntity[] $result */
    $result = $vectorStore->similaritySearch($embedding, 2, ['type' => 'city']);

    // We check that the search return the correct entities in the right order
    expect($result[0]->content)->toBe('I live in Paris');
});

it('tests a full embedding flow with Doctrine', function () {
    $config = ORMSetup::createAttributeMetadataConfiguration(
        [__DIR__.'/src'],
        true
    );

    $connectionParams = [
        'dbname' => 'postgres',
        'user' => 'myuser',
        'password' => '!ChangeMe!',
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
    ];

    $connection = DriverManager::getConnection($connectionParams);
    $connection->executeQuery('TRUNCATE TABLE test_place');
    $entityManager = new EntityManager($connection, $config);

    $filePath = __DIR__.'/PlacesTextFiles';
    $reader = new TextFileDataReader($filePath, PlaceEntity::class);
    $documents = $reader->getDocuments();
    $splittedDocuments = DocumentSplitter::splitDocuments($documents, 200);
    $formattedDocuments = EmbeddingFormatter::formatEmbeddings($splittedDocuments);

    $embeddingGenerator = new OpenAIEmbeddingGenerator();
    $embededDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);

    $vectorStore = new DoctrineVectorStore($entityManager, PlaceEntity::class);
    $vectorStore->addDocuments($embededDocuments);

    $embedding = $embeddingGenerator->embedText('France pronunciation');
    /** @var PlaceEntity[] $result */
    $result = $vectorStore->similaritySearch($embedding, 2);

    // We check that the search return the correct entities in the right order
    expect(explode(' ', $result[0]->content)[0])->toBe('France');
});

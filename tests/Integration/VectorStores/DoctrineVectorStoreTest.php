<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use LLPhant\Embeddings\OpenAIEmbeddings;
use LLPhant\VectorStores\DoctrineVectorStore;
use LLPhant\VectorStores\ExampleEmbeddingEntity;

it('Create one embedding and store it in a postgresql database', function () {
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
    $connection->executeQuery('TRUNCATE TABLE embeddings');
    $entityManager = new EntityManager($connection, $config);

    $vectorStore = new DoctrineVectorStore($entityManager);
    $llm = new OpenAIEmbeddings();

    $food = new ExampleEmbeddingEntity();
    $food->data = 'I love food';
    $food->type = 'food';
    $embedding = $llm->embedText($food->data);
    $vectorStore->saveEmbedding($embedding, $food);

    // We check that the embedding is saved in the database
    expect($food->embedding)->toBeString();

    $paris = new ExampleEmbeddingEntity();
    $paris->data = 'I live in Paris';
    $paris->type = 'city';
    $embedding = $llm->embedText($paris->data);
    $vectorStore->saveEmbedding($embedding, $paris);

    $france = new ExampleEmbeddingEntity();
    $france->data = 'I live in France';
    $france->type = 'country';
    $embedding = $llm->embedText($france->data);
    $vectorStore->saveEmbedding($embedding, $france);

    $embedding = $llm->embedText('I live in Asia');
    /** @var ExampleEmbeddingEntity[] $result */
    $result = $vectorStore->similaritySearch($embedding, ExampleEmbeddingEntity::class, 2, ['type' => 'city']);

    // We check that the search return the correct entities in the right order
    expect($result[0]->data)->toBe('I live in Paris');
});

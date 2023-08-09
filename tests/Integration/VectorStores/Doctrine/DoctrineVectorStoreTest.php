<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineVectorStore;
use Tests\Integration\VectorStores\Doctrine\PlaceEntity;

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

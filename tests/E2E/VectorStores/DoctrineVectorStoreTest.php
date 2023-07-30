<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use LLPhant\Embeddings\OpenAIEmbeddings;
use LLPhant\VectorStores\DoctrineVectorStore;
use Tests\E2E\VectorStores\ExampleEmbeddingEntity;

it('Create one embedding and store it in a postgresql database', function () {
    $llm = new OpenAIEmbeddings();
    $embedding = $llm->embedText("I love food");

    $config = ORMSetup::createAttributeMetadataConfiguration(
        paths: array(__DIR__."/src"),
    );

    Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');

    $connectionParams = [
        'dbname' => 'postgres',
        'user' => 'myuser',
        'password' => '!ChangeMe!',
        'host' => 'localhost',
        'driver' => 'pdo_pgsql',
    ];

    $connection = DriverManager::getConnection($connectionParams);
    $entityManager = new EntityManager($connection, $config);
    $vectorStore = new DoctrineVectorStore($entityManager);
    $entity = new ExampleEmbeddingEntity();
    $entity->data = "I love food";

    $vectorStore->saveEmbedding($embedding, $entity);

    expect($entity->embedding)->toBeString();
});

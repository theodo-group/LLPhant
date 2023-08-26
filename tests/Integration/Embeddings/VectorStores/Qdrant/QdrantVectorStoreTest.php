<?php

namespace Tests\Integration\Embeddings\VectorStores\Qdrant;

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingFormatter\EmbeddingFormatter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Qdrant\QdrantVectorStore;
use Qdrant\Config;

it('tests a full embedding flow with Qdrant', function () {
    $filePath = __DIR__.'/../PlacesTextFiles';
    $reader = new FileDataReader($filePath, Document::class);
    $documents = $reader->getDocuments();
    $splittedDocuments = DocumentSplitter::splitDocuments($documents, 200);
    $formattedDocuments = EmbeddingFormatter::formatEmbeddings($splittedDocuments);

    $embeddingGenerator = new OpenAIEmbeddingGenerator();
    $embededDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);

    $host = getenv('QDRANT_HOST');
    $apiKey = getenv('QDRANT_API_KEY');
    $config = new Config($host);
    $config->setApiKey($apiKey);

    $collectionName = 'places2';
    $vectorStore = new QdrantVectorStore($config, $collectionName);
    $vectorStore->addDocuments($embededDocuments);
    $embedding = $embeddingGenerator->embedText('France the country');
    /** @var Document[] $result */
    $result = $vectorStore->similaritySearch($embedding, 2);

    // We check that the search return the correct entities in the right order
    expect(explode(' ', $result[0]->content)[0])->toBe('France');
});

<?php

declare(strict_types=1);

namespace Tests\Integration\Query\Embeddings;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAIADA002EmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\Memory\MemoryVectorStore;
use LLPhant\Query\SemanticSearch\QuestionAnswering;

it('generates a answer based on private knowledge', function () {
    $dataReader = new FileDataReader(__DIR__.'/private-data.txt');
    $documents = $dataReader->getDocuments();

    $splittedDocuments = DocumentSplitter::splitDocuments($documents, 500);

    $embeddingGenerator = new OpenAIADA002EmbeddingGenerator();
    $embeddedDocuments = $embeddingGenerator->embedDocuments($splittedDocuments);

    $memoryVectorStore = new MemoryVectorStore();
    $memoryVectorStore->addDocuments($embeddedDocuments);

    $qa = new QuestionAnswering(
        $memoryVectorStore,
        $embeddingGenerator,
        new OpenAIChat()
    );

    $answer = $qa->answerQuestion('what is the secret of Alice?');

    expect($answer)->toContain('cheese');
});

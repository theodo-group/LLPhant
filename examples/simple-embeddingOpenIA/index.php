<?php

use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\Query\SemanticSearch\QuestionAnswering;

require_once 'vendor/autoload.php';

if (file_exists(__DIR__ . '/documents-vectorStore.json')) {

    $question = "How to use autowiring to Automate the configuration  of application services ?";

    $vectorStore = new FileSystemVectorStore();
    $embeddingGenerator = new OpenAIEmbeddingGenerator();
    $embeddingGenerator->modelName = 'text-embedding-3-small';
    $embeddingGenerator->EmbeddingLength = 512;

    $qa = new QuestionAnswering(
        $vectorStore,
        $embeddingGenerator,
        new OpenAIChat()
    );

    echo $answer = $qa->answerQuestion($question);

} else {
    echo "The documents-vectorStore.json file does not exist. Please run the script.php to generate it.";
}

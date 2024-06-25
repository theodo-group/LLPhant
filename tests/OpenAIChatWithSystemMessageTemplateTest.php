<?php

declare(strict_types=1);

namespace Tests;

use LLPhant\Chat\ChatInterface;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OpenAIConfig;
use LLPhant\Query\SemanticSearch\QuestionAnswering;

function chat(): ChatInterface
{
    $config = new OpenAIConfig();

    return new OpenAIChat($config);
}

it('can generate some stuff', function () {
    $filesVectorStore = new FileSystemVectorStore();
    $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator();
    $qa = new QuestionAnswering(
        $filesVectorStore,
        $embeddingGenerator,
        chat()
    );

    $qa->systemMessageTemplate = 'your name is Ciro. \\n\\n{context}.';

    $response = $qa->answerQuestion('what is your name ?');
    expect($response)->toContain('Ciro');
});

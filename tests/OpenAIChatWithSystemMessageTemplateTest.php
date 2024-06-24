<?php

declare(strict_types=1);

namespace Tests;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OpenAIConfig;
use LLPhant\Query\SemanticSearch\QuestionAnswering;

it('can generate some stuff', function () {
    $filesVectorStore = new FileSystemVectorStore();
    $embeddingGenerator = new OpenAI3SmallEmbeddingGenerator();
    $chat = new OpenAIChat();
  
    //if ($filesVectorStore->getNumberOfDocuments() === 0) {
      //$dataReader = new FileDataReader(__DIR__.'/text.txt');
      //$documents = $dataReader->getDocuments();
      //$splittedDocuments = DocumentSplitter::splitDocuments($documents, 2000);
      //$formattedDocuments = EmbeddingFormatter::formatEmbeddings($splittedDocuments);
      //$embeddedDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);
      //$filesVectorStore->addDocuments($embeddedDocuments);
    //}

    $qa = new QuestionAnswering(
      $filesVectorStore,
      $embeddingGenerator,
      $chat
    );
  
    $qa->systemMessageTemplate = 'your name is Ciro. \\n\\n{context}.';

    $response = $chat->generateText('what is your name ?');
    expect(str_contains($response, 'Ciro'))->toBeTrue();
});

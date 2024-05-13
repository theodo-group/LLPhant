<?php

declare(strict_types=1);

namespace Tests\Unit\Query\SemanticSearch;

use LLPhant\Chat\ChatInterface;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use Mockery;

beforeEach(function () {
    $this->question = 'What is the capital city of Italy?';
    $this->answer = 'The capital city of Italy is Rome';

    $this->docs = getDocuments();

    $this->vectorStore = Mockery::mock(VectorStoreBase::class);
    $this->vectorStore->allows([
        'similaritySearch' => $this->docs,
    ]);

    $this->embedding = Mockery::mock(EmbeddingGeneratorInterface::class);
    $this->embedding->allows([
        'embedText' => [],
    ]);

    $this->chat = Mockery::mock(ChatInterface::class);
    $this->chat->allows([
        'setSystemMessage' => null,
        'generateText' => $this->answer,
    ]);
});

it('answer question', function () {

    $qa = new QuestionAnswering($this->vectorStore, $this->embedding, $this->chat);

    $result = $qa->answerQuestion($this->question);

    expect($result)->toBe($this->answer);
});

it('retrieved Documents', function () {
    $qa = new QuestionAnswering($this->vectorStore, $this->embedding, $this->chat);

    $result = $qa->answerQuestion($this->question);
    $docs = $qa->getRetrievedDocuments();

    expect($docs)->toBe($this->docs);
});

/**
 * @return Document[]
 */
function getDocuments(): array
{
    $doc1 = new Document;
    $doc1->content = 'Rome is the capital city of Italy';
    $doc2 = new Document;
    $doc2->content = 'Rome is also the capital of the Lazio region';
    $doc3 = new Document;
    $doc3->content = 'The Metropolitan City of Rome, with a population of 4,355,725 residents';
    $doc4 = new Document;
    $doc4->content = 'Rome is often referred to as the City of Seven Hills due to its geographic location, and also as the "Eternal City"';

    return [$doc1, $doc2, $doc3, $doc4];
}

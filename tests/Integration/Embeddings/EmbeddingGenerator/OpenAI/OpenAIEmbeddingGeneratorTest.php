<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAI3SmallEmbeddingGenerator;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAI\OpenAIADA002EmbeddingGenerator;

it('can embed some stuff', function () {
    $llm = new OpenAIADA002EmbeddingGenerator();
    $embedding = $llm->embedText('I love food');
    expect($embedding[0])->toBeFloat();
});

it('can embed batch stuff', function () {
    $llm = new OpenAI3SmallEmbeddingGenerator();

    $doc1 = new Document();
    $doc1->content = 'I love Italian food';

    $doc2 = new Document();
    $doc2->content = 'I love French food';

    $docs = $llm->embedDocuments([$doc1, $doc2]);
    expect($docs[0]->embedding[0])->toBeFloat();
});

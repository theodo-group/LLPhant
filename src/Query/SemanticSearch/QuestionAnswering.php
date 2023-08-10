<?php

namespace LLPhant\Query\SemanticSearch;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;

class QuestionAnswering
{
    public string $prompt = "Use the following pieces of context to answer the question at the end. If you don't know the answer, just say that you don't know, don't try to make up an answer.\n\n{context}\n\nQuestion: {question}\nHelpful Answer:";

    public function __construct(public readonly VectorStoreBase $vectorStoreBase, public readonly EmbeddingGeneratorInterface $embeddingGenerator, public readonly OpenAIChat $openAIChat)
    {
    }

    public function answerQuestion(string $question): string
    {
        $embedding = $this->embeddingGenerator->embedText($question);
        /** @var Document[] $documents */
        $documents = $this->vectorStoreBase->similaritySearch($embedding);

        if ($documents === []) {
            return "I don't know. I didn't find any document to answer the question";
        }

        $context = '';
        foreach ($documents as $document) {
            $context .= $document->content.' ';
        }
        $prompt = $this->getPrompt($context, $question);

        return $this->openAIChat->generateText($prompt);
    }

    private function getPrompt(string $context, string $question): string
    {
        return str_replace('{context}', $context, str_replace('{question}', $question, $this->prompt));
    }
}

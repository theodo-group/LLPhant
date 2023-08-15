<?php

namespace LLPhant\Query\SemanticSearch;

use LLPhant\Chat\Message;
use LLPhant\Chat\OpenAIChat;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionAnswering
{
    public string $systemMessageTemplate = "Use the following pieces of context to answer the question of the user. If you don't know the answer, just say that you don't know, don't try to make up an answer.\n\n{context}.";

    public function __construct(public readonly VectorStoreBase $vectorStoreBase, public readonly EmbeddingGeneratorInterface $embeddingGenerator, public readonly OpenAIChat $openAIChat)
    {
    }

    public function answerQuestion(string $question): string
    {
        $prompt = $this->searchDocumentAndCreateSystemMessage($question);

        return $this->openAIChat->generateText($prompt);
    }

    public function answerQuestionStream(string $question): string
    {
        $prompt = $this->searchDocumentAndCreateSystemMessage($question);

        return $this->openAIChat->generateStreamOfText($prompt);
    }

    /**
     * @param  Message[]  $messages
     */
    public function answerQuestionFromChat(array $messages): StreamedResponse
    {
        // First we need to give the context to openAI with the good instructions
        $userQuestion = $messages[count($messages) - 1]->content;
        $systemMessage = $this->searchDocumentAndCreateSystemMessage($userQuestion);
        $this->openAIChat->setSystemMessage($systemMessage);

        // Then we can just give the conversation
        return $this->openAIChat->generateChatStream($messages);
    }

    private function searchDocumentAndCreateSystemMessage(string $question): string
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

        return $this->getSystemMessage($context);
    }

    private function getSystemMessage(string $context): string
    {
        return str_replace('{context}', $context, $this->systemMessageTemplate);
    }
}

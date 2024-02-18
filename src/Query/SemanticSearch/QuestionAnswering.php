<?php

namespace LLPhant\Query\SemanticSearch;

use LLPhant\Chat\ChatInterface;
use LLPhant\Chat\Message;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use Psr\Http\Message\StreamInterface;

class QuestionAnswering
{
    public string $systemMessageTemplate = "Use the following pieces of context to answer the question of the user. If you don't know the answer, just say that you don't know, don't try to make up an answer.\n\n{context}.";

    public function __construct(public readonly VectorStoreBase $vectorStoreBase, public readonly EmbeddingGeneratorInterface $embeddingGenerator, public readonly ChatInterface $chat)
    {
    }

    /**
     * @param  array<string, string|int>|array<mixed[]>  $additionalArguments
     */
    public function answerQuestion(string $question, int $k = 4, array $additionalArguments = []): string
    {
        $systemMessage = $this->searchDocumentAndCreateSystemMessage($question, $k, $additionalArguments);
        $this->chat->setSystemMessage($systemMessage);

        return $this->chat->generateText($question);
    }

    /**
     * @param  array<string, string|int>|array<mixed[]>  $additionalArguments
     */
    public function answerQuestionStream(string $question, int $k = 4, array $additionalArguments = []): string
    {
        $systemMessage = $this->searchDocumentAndCreateSystemMessage($question, $k, $additionalArguments);
        $this->chat->setSystemMessage($systemMessage);

        return $this->chat->generateStreamOfText($question);
    }

    /**
     * @param  Message[]  $messages
     * @param  array<string, string|int>|array<mixed[]>  $additionalArguments
     */
    public function answerQuestionFromChat(array $messages, int $k = 4, array $additionalArguments = []): StreamInterface
    {
        // First we need to give the context to openAI with the good instructions
        $userQuestion = $messages[count($messages) - 1]->content;
        $systemMessage = $this->searchDocumentAndCreateSystemMessage($userQuestion, $k, $additionalArguments);
        $this->chat->setSystemMessage($systemMessage);

        // Then we can just give the conversation
        return $this->chat->generateChatStream($messages);
    }

    /**
     * @param  array<string, string|int>|array<mixed[]>  $additionalArguments
     */
    private function searchDocumentAndCreateSystemMessage(string $question, int $k, array $additionalArguments): string
    {
        $embedding = $this->embeddingGenerator->embedText($question);
        /** @var Document[] $documents */
        $documents = $this->vectorStoreBase->similaritySearch($embedding, $k, $additionalArguments);

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

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
    /** @var Document[] */
    protected array $retrievedDocs;

    public string $systemMessageTemplate = "Use the following pieces of context to answer the question of the user. If you don't know the answer, just say that you don't know, don't try to make up an answer.\n\n{context}.";

    public function __construct(public readonly VectorStoreBase $vectorStoreBase, public readonly EmbeddingGeneratorInterface $embeddingGenerator, public readonly ChatInterface $chat, private readonly QueryTransformer $queryTransformer = new IdentityTransformer())
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
    public function answerQuestionStream(string $question, int $k = 4, array $additionalArguments = []): StreamInterface
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
     * @return Document[]
     */
    public function getRetrievedDocuments(): array
    {
        return $this->retrievedDocs;
    }

    /**
     * @param  array<string, string|int>|array<mixed[]>  $additionalArguments
     */
    private function searchDocumentAndCreateSystemMessage(string $question, int $k, array $additionalArguments): string
    {
        $questions = $this->queryTransformer->transformQuery($question);

        $this->retrievedDocs = [];

        foreach ($questions as $question) {
            $embedding = $this->embeddingGenerator->embedText($question);
            $docs = $this->vectorStoreBase->similaritySearch($embedding, $k, $additionalArguments);
            foreach ($docs as $doc) {
                $this->retrievedDocs[\md5($doc->content)] = $doc;
            }
        }

        $context = '';
        $i = 0;
        foreach ($this->retrievedDocs as $document) {
            if ($i >= $k) {
                break;
            }
            $i++;
            $context .= $document->content.' ';
        }

        // Ensure retro-compatibility
        $this->retrievedDocs = \array_values($this->retrievedDocs);

        return $this->getSystemMessage($context);
    }

    private function getSystemMessage(string $context): string
    {
        return str_replace('{context}', $context, $this->systemMessageTemplate);
    }
}

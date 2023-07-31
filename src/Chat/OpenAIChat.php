<?php

namespace LLPhant\Chat;

use function getenv;
use LLPhant\Chat\Enums\ChatRole;
use LLPhant\Chat\Enums\OpenAIChatModel;
use Mockery\Exception;
use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;

final class OpenAIChat extends Chat
{
    private readonly Client $client;

    private readonly string|OpenAIChatModel $model;

    private Message $systemMessage;

    public function __construct(OpenAIChatConfig $config = null)
    {
        $apiKey = $config->apiKey ?? getenv('OPENAI_API_KEY');
        if (! $apiKey) {
            throw new Exception('You have to provide a OPENAI_API_KEY env var to request OpenAI .');
        }
        $this->client = OpenAI::client($apiKey);
        $this->model = $config->model ?? OpenAIChatModel::Gpt4;
    }

    /**
     * We only need one system message in most of the case
     */
    public function setSystemMessage(string $message): void
    {
        $systemMessage = new Message();
        $systemMessage->role = ChatRole::System;
        $systemMessage->content = $message;
        $this->systemMessage = $systemMessage;
    }

    public function generate(string $prompt): CreateResponse
    {
        $messages = [];
        if (isset($this->systemMessage)) {
            $messages[] = $this->systemMessage;
        }

        $userMessage = new Message();
        $userMessage->role = ChatRole::User;
        $userMessage->content = $prompt;
        $messages[] = $userMessage;

        return $this->client->chat()->create(
            ['model' => $this->model->getModelName(),
                'messages' => $messages,
            ]
        );
    }
}

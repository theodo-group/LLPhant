<?php

namespace LLPhant\LLMs;

use LLPhant\Chat\Enums\ChatRole;
use LLPhant\Chat\Message;
use Mockery\Exception;
use OpenAI;
use OpenAI\Client;
use LLPhant\LLMs\Enums\OpenAIChatModel;

use OpenAI\Responses\Chat\CreateResponse;
use function getenv;


class OpenAIChat extends BaseLLM
{
    private Client $client;
    private OpenAIChatModel $model;

    private Message $systemMessage;

    public function __construct() {
        parent::__construct();

        $apiKey = getenv('OPENAI_API_KEY');
        if (!$apiKey) {
            throw new Exception('You have to provide a OPENAI_API_KEY env var to request OpenAI .');
        }
        $this->client = OpenAI::client($apiKey);
        $this->model = OpenAIChatModel::Gpt4;


//        if (isset($config['model_name'])) {
//            $this->model = OpenAIChatModel::from($config['model_name']);
//        } else {
//            $this->model = OpenAIChatModel::Gpt35Turbo;
//        }
    }

    /**
     * We only need one system message in most of the case
     *
     * @param string $message
     * @return void
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

        $response = $this->client->chat()->create(
            ['model' => $this->model->getModelName(),
                'messages' => $messages
            ]
        );

        return $response;
    }
}

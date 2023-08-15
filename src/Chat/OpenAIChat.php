<?php

namespace LLPhant\Chat;

use LLPhant\Chat\Enums\ChatRole;
use LLPhant\Chat\Enums\OpenAIChatModel;
use LLPhant\OpenAIConfig;
use Mockery\Exception;
use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function getenv;

final class OpenAIChat
{
    private readonly Client $client;

    private readonly string $model;

    private Message $systemMessage;

    public function __construct(OpenAIConfig $config = null)
    {
        $apiKey = $config->apiKey ?? getenv('OPENAI_API_KEY');
        if (! $apiKey) {
            throw new Exception('You have to provide a OPENAI_API_KEY env var to request OpenAI .');
        }
        $this->client = OpenAI::client($apiKey);
        $this->model = $config->model ?? OpenAIChatModel::Gpt4->getModelName();
    }

    public function generateText(string $prompt): string
    {
        $answer = $this->generate($prompt);

        return $answer->choices[0]->message->content ?? '';
    }

    public function generateStreamOfText(string $prompt): StreamedResponse
    {
        $messages = $this->createOpenAIMessagesFromPrompt($prompt);

        return $this->createStreamedResponse($messages);
    }

    /**
     * @param  Message[]  $messages
     */
    public function generateChatStream(array $messages): StreamedResponse
    {
        return $this->createStreamedResponse($messages);
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

    private function generate(string $prompt): CreateResponse
    {
        $messages = $this->createOpenAIMessagesFromPrompt($prompt);
        $messages = $this->addSystemMessageToMessages($messages);

        return $this->client->chat()->create(
            [
                'model' => $this->model,
                'messages' => $messages,
            ]
        );
    }

    /**
     * @return Message[]
     */
    private function createOpenAIMessagesFromPrompt(string $prompt): array
    {
        $userMessage = new Message();
        $userMessage->role = ChatRole::User;
        $userMessage->content = $prompt;

        return [$userMessage];
    }

    /**
     * @param  Message[]  $messages
     * @return Message[]
     */
    private function addSystemMessageToMessages(array $messages = []): array
    {
        $finalMessages = [];
        if (isset($this->systemMessage)) {
            $finalMessages[] = $this->systemMessage;
        }

        return array_merge($finalMessages, $messages);
    }

    /**
     * @param  Message[]  $messages
     */
    private function createStreamedResponse(array $messages): StreamedResponse
    {
        $messages = $this->addSystemMessageToMessages($messages);
        $stream = $this->client->chat()->createStreamed(
            [
                'model' => $this->model,
                'messages' => $messages,
            ]
        );
        $response = new StreamedResponse();
        //We need this to make the streaming works
        //It may not work with Symfony: https://stackoverflow.com/questions/76362863/why-streamedresponse-from-symfony-6-is-sent-at-once
        @ob_end_clean();

        $response->setCallback(function () use ($stream): void {
            /** @var CreateStreamedResponse $partialResponse */
            foreach ($stream as $partialResponse) {
                if (! ($partialResponse->choices[0]->delta->content)) {
                    continue;
                }
                echo $partialResponse->choices[0]->delta->content;
            }
        });

        return $response->send();
    }
}

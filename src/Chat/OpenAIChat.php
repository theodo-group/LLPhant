<?php

namespace LLPhant\Chat;

use Exception;
use LLPhant\Chat\Enums\ChatRole;
use LLPhant\Chat\Enums\OpenAIChatModel;
use LLPhant\Chat\Function\FunctionFormatter;
use LLPhant\Chat\Function\FunctionInfo;
use LLPhant\OpenAIConfig;
use OpenAI;
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateResponseFunctionCall;
use OpenAI\Responses\Chat\CreateStreamedResponse;
use OpenAI\Responses\Chat\CreateStreamedResponseFunctionCall;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function getenv;

final class OpenAIChat
{
    private readonly Client $client;

    public string $model;

    private Message $systemMessage;

    /** @var FunctionInfo[] */
    private array $functions = [];

    public ?FunctionInfo $lastFunctionCalled = null;

    public ?FunctionInfo $requiredFunction = null;

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
        $this->handleFunctionCall($answer);

        return $answer->choices[0]->message->content ?? '';
    }

    public function generateTextOrReturnFunctionCalled(string $prompt): string|FunctionInfo
    {
        $answer = $this->generate($prompt);
        $functionToCall = $this->getFunctionToCall($answer);

        if ($functionToCall instanceof FunctionInfo) {
            $this->lastFunctionCalled = $functionToCall;

            return $functionToCall;
        }

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

    /**
     * @param  FunctionInfo[]  $functions
     */
    public function setFunctions(array $functions): void
    {
        $this->functions = $functions;
    }

    public function addFunction(FunctionInfo $functionInfo): void
    {
        $this->functions[] = $functionInfo;
    }

    private function generate(string $prompt): CreateResponse
    {
        $messages = $this->createOpenAIMessagesFromPrompt($prompt);
        $openAiArgs = $this->getOpenAiArgs($messages);

        return $this->client->chat()->create($openAiArgs);
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
     */
    private function createStreamedResponse(array $messages): StreamedResponse
    {
        $openAiArgs = $this->getOpenAiArgs($messages);

        $stream = $this->client->chat()->createStreamed($openAiArgs);
        $response = new StreamedResponse();
        //We need this to make the streaming works
        //It may not work with Symfony: https://stackoverflow.com/questions/76362863/why-streamedresponse-from-symfony-6-is-sent-at-once
        @ob_end_clean();

        $response->setCallback(function () use ($stream): void {
            $arguments = '';
            $functionName = null;
            /** @var CreateStreamedResponse $partialResponse */
            foreach ($stream as $partialResponse) {
                $responseFunctionCall = $partialResponse->choices[0]->delta->functionCall;
                if ($responseFunctionCall instanceof CreateStreamedResponseFunctionCall) {
                    if (! is_null($responseFunctionCall->name)) {
                        $functionName = $responseFunctionCall->name;
                    }
                    if (! is_null($responseFunctionCall->arguments)) {
                        $arguments .= $responseFunctionCall->arguments;
                    }
                }
                // $functionName should be always set if finishReason is function_call
                if ($partialResponse->choices[0]->finishReason === 'function_call' && $functionName) {
                    $this->callFunction($functionName, $arguments);
                }
                if (! is_null($partialResponse->choices[0]->finishReason)) {
                    ob_start();
                    break;
                }
                if (! ($partialResponse->choices[0]->delta->content)) {
                    continue;
                }
                echo $partialResponse->choices[0]->delta->content;
            }
        });

        return $response->send();
    }

    /**
     * @param  Message[]  $messages
     * @return array{model: string, messages: Message[], functions?: mixed[]}
     */
    private function getOpenAiArgs(array $messages): array
    {
        // The system message should be the first
        $finalMessages = [];
        if (isset($this->systemMessage)) {
            $finalMessages[] = $this->systemMessage;
        }

        $finalMessages = array_merge($finalMessages, $messages);

        $openAiArgs = [
            'model' => $this->model,
            'messages' => $finalMessages,
        ];

        if ($this->functions !== []) {
            $openAiArgs['functions'] = FunctionFormatter::formatFunctionsToOpenAI($this->functions);
        }

        if ($this->requiredFunction instanceof FunctionInfo) {
            $openAiArgs['function_call'] =
                ['name' => $this->requiredFunction->name];
        }

        return $openAiArgs;
    }

    /**
     * @throws \JsonException
     */
    private function handleFunctionCall(CreateResponse $answer): void
    {
        if ($answer->choices[0]->message->functionCall instanceof CreateResponseFunctionCall) {
            $functionName = $answer->choices[0]->message->functionCall->name;
            $arguments = $answer->choices[0]->message->functionCall->arguments;

            $this->callFunction($functionName, $arguments);
        }
    }

    private function getFunctionToCall(CreateResponse $answer): ?FunctionInfo
    {
        if ($answer->choices[0]->message->functionCall instanceof CreateResponseFunctionCall) {
            $functionName = $answer->choices[0]->message->functionCall->name;
            $arguments = $answer->choices[0]->message->functionCall->arguments;
            $functionInfo = $this->getFunctionInfoFromName($functionName);
            $functionInfo->jsonArgs = $arguments;

            return $functionInfo;
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function getFunctionInfoFromName(string $functionName): FunctionInfo
    {
        foreach ($this->functions as $function) {
            if ($function->name === $functionName) {
                return $function;
            }
        }

        throw new Exception("OpenAI tried to call $functionName which doesn't exist");
    }

    private function callFunction(string $functionName, string $arguments): void
    {
        $arguments = json_decode($arguments, true, 512, JSON_THROW_ON_ERROR);
        $functionToCall = $this->getFunctionInfoFromName($functionName);
        $functionToCall->instance->{$functionToCall->name}(...$arguments);
        $this->lastFunctionCalled = $functionToCall;
    }
}

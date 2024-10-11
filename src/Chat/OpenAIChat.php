<?php

namespace LLPhant\Chat;

use Exception;
use GuzzleHttp\Psr7\Utils;
use LLPhant\Chat\Enums\ChatRole;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\ToolFormatter;
use LLPhant\Exception\MissingParameterException;
use LLPhant\OpenAIConfig;
use OpenAI;
use OpenAI\Contracts\ClientContract;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateResponseToolCall;
use OpenAI\Responses\Chat\CreateStreamedResponseToolCall;
use OpenAI\Responses\StreamResponse;
use Psr\Http\Message\StreamInterface;

/**
 * @phpstan-import-type ModelOptions from OpenAIConfig
 */
class OpenAIChat implements ChatInterface
{
    private readonly ClientContract $client;

    public string $model;

    private ?CreateResponse $lastResponse = null;

    private int $totalTokens = 0;

    /** @var array<string, mixed> */
    private array $modelOptions = [];

    private Message $systemMessage;

    /** @var FunctionInfo[] */
    private array $tools = [];

    public ?FunctionInfo $lastFunctionCalled = null;

    public ?FunctionInfo $requiredFunction = null;

    /**
     * @throws MissingParameterException
     */
    public function __construct(protected OpenAIConfig $config = new OpenAIConfig())
    {
        if ($config->client instanceof \OpenAI\Contracts\ClientContract) {
            $this->client = $config->client;
        } else {
            if (! $config->apiKey) {
                throw new MissingParameterException('You have to provide a OPENAI_API_KEY env var to request OpenAI.');
            }

            $this->client = $config->client ?? OpenAI::factory()
                ->withBaseUri($config->url)
                ->withApiKey($config->apiKey)
                ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
                ->make();
        }
        $this->model = $config->model;
        $this->modelOptions = $config->modelOptions ?? [];
    }

    public function generateText(string $prompt): string
    {
        $answer = $this->generate($prompt);

        $this->handleTools($answer);

        return $this->responseToString($answer);
    }

    public function getLastResponse(): ?CreateResponse
    {
        return $this->lastResponse;
    }

    public function getTotalTokens(): int
    {
        return $this->totalTokens;
    }

    public function generateTextOrReturnFunctionCalled(string $prompt): string|FunctionInfo
    {
        $this->lastFunctionCalled = null;
        $answer = $this->generate($prompt);

        $toolsToCall = $this->getToolsToCall($answer);

        foreach ($toolsToCall as $toolToCall) {
            $this->lastFunctionCalled = $toolToCall;
        }

        if ($this->lastFunctionCalled instanceof FunctionInfo) {
            return $this->lastFunctionCalled;
        }

        return $this->responseToString($answer);
    }

    public function generateStreamOfText(string $prompt): StreamInterface
    {
        $messages = $this->createOpenAIMessagesFromPrompt($prompt);

        return $this->createStreamedResponse($messages);
    }

    /**
     * @param  Message[]  $messages
     */
    public function generateChat(array $messages): string
    {
        $answer = $this->generateResponseFromMessages($messages);

        $this->handleTools($answer);

        return $this->responseToString($answer);
    }

    public function generateChatOrReturnFunctionCalled(array $messages): string|FunctionInfo
    {
        $answer = $this->generateResponseFromMessages($messages);

        $toolsToCall = $this->getToolsToCall($answer);

        $this->lastFunctionCalled = null;
        foreach ($toolsToCall as $toolToCall) {
            $this->lastFunctionCalled = $toolToCall;
        }

        if ($this->lastFunctionCalled instanceof FunctionInfo) {
            return $this->lastFunctionCalled;
        }

        return $this->responseToString($answer);
    }

    /**
     * @param  Message[]  $messages
     */
    public function generateChatStream(array $messages): StreamInterface
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
     * @param  FunctionInfo[]  $tools
     */
    public function setTools(array $tools): void
    {
        $this->tools = $tools;
    }

    public function addTool(FunctionInfo $functionInfo): void
    {
        $this->tools[] = $functionInfo;
    }

    /**
     * @deprecated Use setTools instead
     *
     * @param  FunctionInfo[]  $functions
     */
    public function setFunctions(array $functions): void
    {
        $this->tools = $functions;
    }

    /**
     * @deprecated Use addTool instead
     */
    public function addFunction(FunctionInfo $functionInfo): void
    {
        $this->tools[] = $functionInfo;
    }

    public function setModelOption(string $option, mixed $value): void
    {
        $this->modelOptions[$option] = $value;
    }

    private function generate(string $prompt): CreateResponse
    {
        $messages = $this->createOpenAIMessagesFromPrompt($prompt);

        return $this->generateResponseFromMessages($messages);
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
    private function createStreamedResponse(array $messages): StreamInterface
    {
        $openAiArgs = $this->getOpenAiArgs($messages);
        $stream = $this->client->chat()->createStreamed($openAiArgs);
        $generator = function (StreamResponse $stream) {
            foreach ($stream as $partialResponse) {
                $toolCalls = $partialResponse->choices[0]->delta->toolCalls ?? [];
                $toolsCalled = [];
                /** @var CreateStreamedResponseToolCall $toolCall */
                foreach ($toolCalls as $toolCall) {
                    $toolsCalled[] = [
                        'function' => $toolCall->function->name,
                        'arguments' => $toolCall->function->arguments,
                    ];
                }

                // $functionName should be always set if finishReason is function_call
                if ($partialResponse->choices[0]->finishReason === 'function_call' && $toolsCalled !== []) {
                    foreach ($toolsCalled as $toolCalled) {
                        if (is_string($toolCalled['function']) && is_string($toolCalled['arguments'])) {
                            $this->callFunction($toolCalled['function'], $toolCalled['arguments']);
                        }
                    }
                }

                if (! is_null($partialResponse->choices[0]->finishReason)) {
                    break;
                }

                if ($partialResponse->choices[0]->delta->content === null) {
                    continue;
                }

                if ($partialResponse->choices[0]->delta->content === '') {
                    continue;
                }

                yield $partialResponse->choices[0]->delta->content;
            }
        };

        return Utils::streamFor($generator($stream));
    }

    /**
     * @param  Message[]  $messages
     * @return array<string, mixed>
     */
    private function getOpenAiArgs(array $messages): array
    {
        // The system message should be the first
        $finalMessages = [];
        if (isset($this->systemMessage)) {
            $finalMessages[] = $this->systemMessage;
        }

        $finalMessages = array_merge($finalMessages, $messages);

        $openAiArgs = $this->modelOptions;

        $openAiArgs = [...$openAiArgs, 'model' => $this->model, 'messages' => $finalMessages];

        if ($this->tools !== []) {
            $openAiArgs['tools'] = ToolFormatter::formatFunctionsToOpenAITools($this->tools);
        }

        if ($this->requiredFunction instanceof FunctionInfo) {
            $openAiArgs['tool_choice'] = ToolFormatter::formatToolChoice($this->requiredFunction);
        }

        return $openAiArgs;
    }

    /**
     * @throws \JsonException
     */
    private function handleTools(CreateResponse $answer): void
    {
        /** @var CreateResponseToolCall $toolCall */
        foreach ($answer->choices[0]->message->toolCalls as $toolCall) {
            $functionName = $toolCall->function->name;
            $arguments = $toolCall->function->arguments;

            $this->callFunction($functionName, $arguments);
        }
    }

    /**
     * @return array<FunctionInfo>
     *
     * @throws Exception
     */
    private function getToolsToCall(CreateResponse $answer): array
    {
        $functionInfos = [];
        /** @var CreateResponseToolCall $toolCall */
        foreach ($answer->choices[0]->message->toolCalls as $toolCall) {
            $functionName = $toolCall->function->name;
            $arguments = $toolCall->function->arguments;
            $functionInfo = $this->getFunctionInfoFromName($functionName);
            $functionInfo->jsonArgs = $arguments;

            $functionInfos[] = $functionInfo;
        }

        return $functionInfos;
    }

    /**
     * @throws Exception
     */
    private function getFunctionInfoFromName(string $functionName): FunctionInfo
    {
        foreach ($this->tools as $function) {
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

    /**
     * @param  Message[]  $messages
     */
    private function generateResponseFromMessages(array $messages): CreateResponse
    {
        $openAiArgs = $this->getOpenAiArgs($messages);
        $answer = $this->client->chat()->create($openAiArgs);
        $this->lastResponse = $answer;
        $this->totalTokens += $answer->usage->totalTokens ?? 0;

        return $answer;
    }

    private function responseToString(CreateResponse $answer): string
    {
        return $answer->choices[0]->message->content ?? '';
    }
}

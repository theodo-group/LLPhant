<?php

namespace LLPhant\Chat;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use LLPhant\AnthropicConfig;
use LLPhant\Chat\Anthropic\AnthropicMessage;
use LLPhant\Chat\Anthropic\AnthropicStreamResponse;
use LLPhant\Chat\Anthropic\AnthropicTotalTokensTrait;
use LLPhant\Chat\FunctionInfo\FunctionFormatter;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Exception\HttpException;
use LLPhant\Exception\MissingParameterException;
use LLPhant\Utility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @phpstan-type ModelOptions array<string, mixed>
 */
class AnthropicChat implements ChatInterface
{
    private ?Message $systemMessage = null;

    /** @var array<string, mixed> */
    private array $modelOptions = [];

    public Client $client;

    private readonly string $model;

    private readonly int $maxTokens;

    /** @var FunctionInfo[] */
    private array $tools = [];

    public ?FunctionInfo $lastFunctionCalled = null;

    private ?AnthropicStreamResponse $streamResponse = null;

    use AnthropicTotalTokensTrait;

    /**
     * @throws MissingParameterException
     */
    public function __construct(AnthropicConfig $config = new AnthropicConfig())
    {
        if ($config->client instanceof \GuzzleHttp\Client) {
            $this->client = $config->client;
        } else {
            if (! $config->apiKey) {
                throw new MissingParameterException('You have to provide a MISTRAL_API_KEY env var to request MistralAI.');
            }
            $this->client = new Client([
                'base_uri' => $config->url,
                'headers' => [
                    'x-api-key' => $config->apiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => $config->version,
                ],
            ]);
        }

        $this->modelOptions = $config->modelOptions;
        $this->model = $config->model;
        $this->maxTokens = $config->maxTokens;
    }

    public function generateText(string $prompt): string
    {
        return $this->generateChat([Message::user($prompt)]);
    }

    /** @param Message[] $messages */
    public function generateChat(array $messages): string
    {
        $params = $this->createParams($messages, false);

        $json = $this->getJsonMessagesResponse($params);

        $responses = $json['content'];

        $result = '';

        /** @var array<string, mixed> $toolsOutput */
        $toolsOutput = [];

        foreach ($responses as $response) {
            if ($response['type'] === 'text') {
                if ($result !== '') {
                    $result .= PHP_EOL;
                }
                $result .= $response['text'];
            }

            if ($response['type'] === 'tool_use') {
                /** @var string $toolId */
                $toolId = $response['id'];
                $toolsOutput[$toolId] = $this->callFunction($response['name'], $response['input']);
            }
        }

        if ($json['stop_reason'] === 'tool_use') {
            return $this->generateChat(\array_merge($messages, [AnthropicMessage::fromAssistantAnswer($responses), AnthropicMessage::toolResultMessage($toolsOutput)]));
        }

        $this->addUsedTokens($json);

        return $result;
    }

    public function generateStreamOfText(string $prompt): StreamInterface
    {
        return $this->generateChatStream([Message::user($prompt)]);
    }

    public function generateChatOrReturnFunctionCalled(array $messages): string|FunctionInfo
    {
        $answer = $this->generateChat($messages);

        if ($this->lastFunctionCalled instanceof FunctionInfo) {
            return $this->lastFunctionCalled;
        }

        return $answer;
    }

    public function generateChatStream(array $messages): StreamInterface
    {
        $params = $this->createParams($messages, true);

        $response = $this->sendRequest($params, true);

        return $this->decodeStreamOfChat($response);
    }

    public function generateTextOrReturnFunctionCalled(string $prompt): string|FunctionInfo
    {
        return $this->generateChatOrReturnFunctionCalled([Message::user($prompt)]);
    }

    public function setSystemMessage(string $message): void
    {
        $this->systemMessage = Message::system($message);
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

    /** @param FunctionInfo[] $functions */
    public function setFunctions(array $functions): void
    {
        $this->setTools($functions);
    }

    public function addFunction(FunctionInfo $functionInfo): void
    {
        $this->addTool($functionInfo);
    }

    public function setModelOption(string $option, mixed $value): void
    {
        $this->modelOptions[$option] = $value;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<mixed>
     *
     * @throws HttpException
     * @throws \LLPhant\Exception\FormatException
     */
    protected function getJsonMessagesResponse(array $params): array
    {
        $response = $this->sendRequest($params, false);

        $contents = $response->getBody()->getContents();

        return Utility::decodeJson($contents);
    }

    /**
     * @param  array<string, mixed>  $json
     *
     * @throws HttpException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function sendRequest(array $json, bool $stream): ResponseInterface
    {
        $response = $this->client->request('POST', 'v1/messages', ['stream' => $stream, 'json' => $json]);
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new HttpException(
                "HTTP error Anthropic ({$status}): ".$response->getBody()->getContents(),
            );
        }

        return $response;
    }

    /**
     * @param  Message[]  $messages
     * @return array<array<string, mixed>>
     */
    private function createMessagesArray(array $messages): array
    {
        $messagesArray = [];

        foreach ($messages as $msg) {
            $messagesArray[] = [
                'role' => $msg->role,
                'content' => $this->getContentFrom($msg),
            ];
        }

        return $messagesArray;
    }

    /**
     * @param  Message[]  $messages
     * @return array<string, mixed>
     **/
    private function createParams(array $messages, bool $stream): array
    {
        $params = [
            ...$this->modelOptions,
            'model' => $this->model,
            'messages' => $this->createMessagesArray($messages),
            'tools' => FunctionFormatter::formatFunctionsToAnthropic($this->tools),
            'max_tokens' => $this->maxTokens,
            'stream' => $stream,
        ];

        if ($this->systemMessage instanceof Message) {
            $params['system'] = $this->systemMessage->content;
        }

        return $params;
    }

    private function decodeStreamOfChat(ResponseInterface $response): StreamInterface
    {
        $this->streamResponse = new AnthropicStreamResponse($response);

        return Utils::streamFor($this->streamResponse->getIterator());
    }

    /**
     * @param  array<string, mixed>  $arguments
     *
     * @throws \Exception
     */
    private function callFunction(string $functionName, array $arguments): mixed
    {
        $functionToCall = $this->getFunctionInfoFromName($functionName);
        $this->lastFunctionCalled = $functionToCall;

        return $functionToCall->callWithArguments($arguments);
    }

    private function getFunctionInfoFromName(string $functionName): FunctionInfo
    {
        foreach ($this->tools as $function) {
            if ($function->name === $functionName) {
                return $function;
            }
        }

        throw new \Exception("AI tried to call $functionName which doesn't exist");
    }

    /**
     * @return string|array<string|int, mixed>
     */
    private function getContentFrom(Message $msg): string|array
    {
        if ($msg instanceof AnthropicMessage) {
            return $msg->contentsArray;
        }

        return $msg->content;
    }

    public function getTotalTokens(): int
    {
        return $this->totalTokens + $this->streamResponse?->getTotalTokens();
    }
}

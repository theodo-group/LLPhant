<?php

declare(strict_types=1);

namespace LLPhant\Chat;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Exception\HttpExcetion;
use LLPhant\Exception\MissingFeatureException;
use LLPhant\Exception\MissingParameterExcetion;
use LLPhant\OllamaConfig;
use LLPhant\Utility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Ollama chat
 *
 * @see https://ollama.ai/
 */
class OllamaChat implements ChatInterface
{
    private ?Message $systemMessage = null;

    private readonly bool $formatJson;

    /** @var array<string, mixed> */
    private array $modelOptions = [];

    public Client $client;

    public function __construct(protected OllamaConfig $config)
    {
        $this->config = $config;
        if (! isset($config->model)) {
            throw new MissingParameterExcetion('You need to specify a model for Ollama');
        }
        $this->client = new Client([
            'base_uri' => $config->url,
        ]);

        $this->formatJson = $config->formatJson;
        $this->modelOptions = $config->modelOptions;
    }

    /**
     * Generate a completion
     *
     * @see https://github.com/ollama/ollama/blob/main/docs/api.md#generate-a-completion
     */
    public function generateText(string $prompt): string
    {
        $params = $params = [
            ...$this->modelOptions,
            'model' => $this->config->model,
            'prompt' => $prompt,
            'stream' => false,
        ];

        if ($this->formatJson) { // force output to be in a json format (in opposition to a text)
            $params['format'] = 'json';
        }

        if ($this->systemMessage instanceof Message) {
            $params['system'] = $this->systemMessage->content;
        }

        $response = $this->sendRequest(
            'POST',
            'generate',
            $params,
        );
        $json = Utility::decodeJson($response->getBody()->getContents());

        return $json['response'];
    }

    /**
     * Ollama does not support (yet) functions, this is an alias of generateText
     */
    public function generateTextOrReturnFunctionCalled(string $prompt): string|FunctionInfo
    {
        return $this->generateText($prompt);
    }

    public function generateStreamOfText(string $prompt): StreamInterface
    {
        $params = [
            ...$this->modelOptions,
            'model' => $this->config->model,
            'prompt' => $prompt,
            'stream' => true,
        ];
        $response = $this->sendRequest(
            'POST',
            'generate',
            $params,
        );

        return $this->decodeStreamOfText($response);
    }

    /**
     * Send a chat request
     *
     * @see https://github.com/ollama/ollama/blob/main/docs/api.md#generate-a-chat-completion
     *
     * @param  Message[]  $messages
     */
    public function generateChat(array $messages): string
    {
        $params = [
            ...$this->modelOptions,
            'model' => $this->config->model,
            'messages' => $this->prepareMessages($messages),
            'stream' => false,
        ];
        $response = $this->sendRequest(
            'POST',
            'chat',
            $params
        );
        $json = Utility::decodeJson($response->getBody()->getContents());

        return $json['message']['content'];
    }

    /** @param  Message[]  $messages */
    public function generateChatStream(array $messages): StreamInterface
    {
        $params = [
            ...$this->modelOptions,
            'model' => $this->config->model,
            'messages' => $this->prepareMessages($messages),
            'stream' => true,
        ];
        $response = $this->sendRequest(
            'POST',
            'chat',
            $params
        );

        return $this->decodeStreamOfChat($response);
    }

    public function setSystemMessage(string $message): void
    {
        $this->systemMessage = Message::system($message);
    }

    /** @param  FunctionInfo[]  $tools */
    public function setTools(array $tools): void
    {
        throw new MissingFeatureException('This feature is not supported');
    }

    public function addTool(FunctionInfo $functionInfo): void
    {
        throw new MissingFeatureException('This feature is not supported');
    }

    /** @param  FunctionInfo[]  $functions */
    public function setFunctions(array $functions): void
    {
        throw new MissingFeatureException('This feature is not supported');
    }

    public function addFunction(FunctionInfo $functionInfo): void
    {
        throw new MissingFeatureException('This feature is not supported');
    }

    public function setModelOption(string $option, mixed $value): void
    {
        $this->modelOptions[$option] = $value;
    }

    /**
     * Send the HTTP request to Ollama API endpoint
     *
     * @param  mixed[]  $json
     *
     * @see https://github.com/ollama/ollama/blob/main/docs/api.md
     */
    protected function sendRequest(string $method, string $path, array $json): ResponseInterface
    {
        $response = $this->client->request($method, $path, ['json' => $json]);
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new HttpExcetion(sprintf(
                'HTTP error from Ollama (%d): %s',
                $status,
                $response->getBody()->getContents()
            ));
        }

        return $response;
    }

    /**
     * Decode a stream of text using the application/x-ndjson format
     */
    protected function decodeStreamOfText(ResponseInterface $response): StreamInterface
    {
        // Split the application/x-ndjson response into json responses
        $stream = explode("\n", $response->getBody()->getContents());
        $generator = function (array $stream) {
            foreach ($stream as $partialResponse) {
                $json = Utility::decodeJson($partialResponse);
                if ((bool) $json['done']) {
                    break;
                }
                if (! isset($json['response'])) {
                    continue;
                }
                if (empty($json['response'])) {
                    continue;
                }
                yield $json['response'];
            }
        };

        return Utils::streamFor($generator($stream));
    }

    /**
     * Decode a stream of chat using the application/x-ndjson format
     */
    protected function decodeStreamOfChat(ResponseInterface $response): StreamInterface
    {
        // Split the application/x-ndjson response into json responses
        $stream = explode("\n", $response->getBody()->getContents());
        $generator = function (array $stream) {
            foreach ($stream as $partialResponse) {
                $json = Utility::decodeJson($partialResponse);
                if ((bool) $json['done']) {
                    break;
                }
                if (! isset($json['message'])) {
                    continue;
                }
                if ($json['message']['role'] !== 'assistant') {
                    continue;
                }
                yield $json['message']['content'];
            }
        };

        return Utils::streamFor($generator($stream));
    }

    /**
     * Prepare the messages for the chat using the format:
     * { "role": "xxx", "content": "yyy"}
     *
     * @param  Message[]  $messages
     * @return mixed[]
     *
     * @see https://github.com/ollama/ollama/blob/main/docs/api.md#generate-a-chat-completion
     */
    protected function prepareMessages(array $messages): array
    {
        $response = [];
        // The system message is always the first
        if (isset($this->systemMessage->role)) {
            $response[] = [
                'role' => $this->systemMessage->role,
                'content' => $this->systemMessage->content,
            ];
        }
        foreach ($messages as $msg) {
            $response[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace LLPhant\Chat;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use LLPhant\Chat\CalledFunction\CalledFunction;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use LLPhant\Chat\FunctionInfo\ToolFormatter;
use LLPhant\Exception\HttpException;
use LLPhant\OllamaConfig;
use LLPhant\Utility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Ollama chat
 *
 * @see https://ollama.ai/
 *
 * @phpstan-import-type ModelOptions from OllamaConfig
 */
class OllamaChat implements ChatInterface
{
    private ?Message $systemMessage = null;

    /** @var array<string, mixed> */
    private array $modelOptions = [];

    public Client $client;

    /** @var FunctionInfo[] */
    private array $tools = [];

    /** @var CalledFunction[] */
    public array $functionsCalled = [];

    public function __construct(protected OllamaConfig $config = new OllamaConfig())
    {
        $this->client = new Client([
            'base_uri' => $config->url,
        ]);

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

    public function generateTextOrReturnFunctionCalled(string $prompt): string|FunctionInfo
    {
        $answer = $this->generateText($prompt);

        if ($this->functionsCalled) {
            $lastKey = array_key_last($this->functionsCalled);

            return $this->functionsCalled[$lastKey]->definition;
        }

        return $answer;
    }

    public function generateChatOrReturnFunctionCalled(array $messages): string|FunctionInfo
    {
        $answer = $this->generateChat($messages);

        if ($this->functionsCalled) {
            $lastKey = array_key_last($this->functionsCalled);

            return $this->functionsCalled[$lastKey]->definition;
        }

        return $answer;
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
            'tools' => ToolFormatter::formatFunctionsToOpenAITools($this->tools),
        ];

        $response = $this->sendRequest(
            'POST',
            'chat',
            $params
        );

        $contents = $response->getBody()->getContents();
        $json = Utility::decodeJson($contents);

        $message = $json['message'];

        /** @var Message[] $toolsOutput */
        $toolsOutput = [];

        if (\array_key_exists('tool_calls', $message)) {
            foreach ($message['tool_calls'] as $toolCall) {
                $functionName = $toolCall['function']['name'];
                $toolResult = $this->callFunction($functionName, $toolCall['function']['arguments']);
                if (is_string($toolResult)) {
                    $toolsOutput[] = Message::toolResult($toolResult);
                }
            }
        }

        if ($toolsOutput !== []) {
            return $this->generateChat(\array_merge($messages, $toolsOutput));
        }

        return $message['content'];
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
            throw new HttpException(sprintf(
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

    /**
     * @param  array<string, mixed>  $arguments
     *
     * @throws \Exception
     */
    private function callFunction(string $functionName, array $arguments): mixed
    {
        $functionToCall = $this->getFunctionInfoFromName($functionName);
        $return = $functionToCall->callWithArguments($arguments);

        $this->functionsCalled[] = new CalledFunction($functionToCall, $arguments, $return);

        return $return;
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
}

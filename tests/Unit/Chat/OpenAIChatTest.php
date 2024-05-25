<?php

namespace Tests\Unit\Chat;

use GuzzleHttp\Psr7\Response;
use LLPhant\Chat\Message;
use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;
use Mockery;
use OpenAI\Client;
use OpenAI\Contracts\TransporterContract;
use OpenAI\ValueObjects\Transporter\Response as TransporterResponse;
use Psr\Http\Message\StreamInterface;

it('no error when construct with no model', function () {
    $config = new OpenAIConfig();
    $config->apiKey = 'fakeapikey';
    $chat = new OpenAIChat($config);
    expect(isset($chat))->toBeTrue();
});

it('returns a stream response using generateStreamOfText()', function () {
    $response = new Response(
        200,
        [],
        'This is the response from OpenAI'
    );
    $transport = Mockery::mock(TransporterContract::class);
    $transport->allows([
        'requestStream' => $response,
    ]);

    $config = new OpenAIConfig();
    $config->client = new Client($transport);
    $chat = new OpenAIChat($config);

    $response = $chat->generateStreamOfText('this is the prompt question');
    expect($response)->toBeInstanceof(StreamInterface::class);
});

it('returns a stream response using generateChatStream()', function () {
    $response = new Response(
        200,
        [],
        'This is the response from OpenAI'
    );
    $transport = Mockery::mock(TransporterContract::class);
    $transport->allows([
        'requestStream' => $response,
    ]);

    $config = new OpenAIConfig();
    $config->client = new Client($transport);
    $chat = new OpenAIChat($config);

    $response = $chat->generateChatStream([Message::user('here the question')]);
    expect($response)->toBeInstanceof(StreamInterface::class);
});

it('returns last response using generateText()', function () {
    $response = TransporterResponse::from(
        fixture('OpenAI/chat-response'),
        ['x-request-id' => '1']
    );
    $transport = Mockery::mock(TransporterContract::class);
    $transport->allows()->requestObject(anyArgs())->andReturns($response);

    $config = new OpenAIConfig();
    $config->client = new Client($transport);
    $chat = new OpenAIChat($config);

    $response = $chat->generateText('here the question');
    $lastResponse = $chat->getLastResponse();

    expect($lastResponse->id)->toBe('chatcmpl-123');
    expect($lastResponse->object)->toBe('chat.completion');
    expect($lastResponse->model)->toBe('gpt-3.5-turbo-0125');
    expect($lastResponse->usage->promptTokens)->toBe(9);
    expect($lastResponse->usage->completionTokens)->toBe(12);
    expect($lastResponse->usage->totalTokens)->toBe(21);
});

it('returns last response using generateTextOrReturnFunctionCalled()', function () {
    $response = TransporterResponse::from(
        fixture('OpenAI/chat-response'),
        ['x-request-id' => '1']
    );
    $transport = Mockery::mock(TransporterContract::class);
    $transport->allows()->requestObject(anyArgs())->andReturns($response);

    $config = new OpenAIConfig();
    $config->client = new Client($transport);
    $chat = new OpenAIChat($config);

    $response = $chat->generateText('here the question');
    $lastResponse = $chat->getLastResponse();
    expect($lastResponse->usage->promptTokens)->toBe(9);
    expect($lastResponse->usage->completionTokens)->toBe(12);
    expect($lastResponse->usage->totalTokens)->toBe(21);
});

it('returns empty (null) last response if no usage', function () {
    $transport = Mockery::mock(TransporterContract::class);

    $config = new OpenAIConfig();
    $config->client = new Client($transport);
    $chat = new OpenAIChat($config);

    expect($chat->getLastResponse())->toBe(null);
});

it('returns total token usage generate() or generateTextOrReturnFunctionCalled()', function () {
    $response = TransporterResponse::from(
        fixture('OpenAI/chat-response'),
        ['x-request-id' => '1']
    );
    $transport = Mockery::mock(TransporterContract::class);
    $transport->allows()->requestObject(anyArgs())->andReturns($response);

    $config = new OpenAIConfig();
    $config->client = new Client($transport);
    $chat = new OpenAIChat($config);

    $response = $chat->generateText('here the question');
    expect($chat->getTotalTokens())->toBe(21);

    $response = $chat->generateTextOrReturnFunctionCalled('here the second question with function');
    expect($chat->getTotalTokens())->toBe(42);
});

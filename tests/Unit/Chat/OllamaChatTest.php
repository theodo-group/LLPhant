<?php

namespace Tests\Unit\Chat;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use LLPhant\Chat\Message;
use LLPhant\Chat\OllamaChat;
use LLPhant\Exception\MissingParameterExcetion;
use LLPhant\OllamaConfig;
use Psr\Http\Message\StreamInterface;

it('error when construct with no model', function () {
    $config = new OllamaConfig();
    $chat = new OllamaChat($config);
})->throws(MissingParameterExcetion::class, 'You need to specify a model for Ollama');

it('no error when construct with model', function () {
    $config = new OllamaConfig();
    $config->model = 'test';
    $chat = new OllamaChat($config);
    expect(isset($chat))->toBeTrue();
});

it('returns a stream response using generateStreamOfText()', function () {
    $mock = new MockHandler([
        new Response(200, [], 'This is the response from Ollama'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $config = new OllamaConfig();
    $config->model = 'test';
    $chat = new OllamaChat($config);
    $chat->client = $client;

    $response = $chat->generateStreamOfText('this is the prompt question');
    expect($response)->toBeInstanceof(StreamInterface::class);
});

it('returns a stream response using generateChatStream()', function () {
    $mock = new MockHandler([
        new Response(200, [], 'This is the response from Ollama'),
    ]);
    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $config = new OllamaConfig();
    $config->model = 'test';
    $chat = new OllamaChat($config);
    $chat->client = $client;

    $response = $chat->generateChatStream([Message::user('here the question')]);
    expect($response)->toBeInstanceof(StreamInterface::class);
});

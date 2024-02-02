<?php

namespace Tests\Unit\Chat;

use GuzzleHttp\Psr7\Response;
use LLPhant\Chat\Message;
use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;
use Mockery;
use OpenAI\Client;
use OpenAI\Contracts\TransporterContract;
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

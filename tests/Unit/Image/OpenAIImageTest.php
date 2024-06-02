<?php

namespace Tests\Unit\Chat;

use GuzzleHttp\Psr7\Response;
namespace LLPhant\Image\Image;
use LLPhant\Image\OpenAIImage;
use LLPhant\OpenAIConfig;
use Mockery;
use OpenAI\Client;
use OpenAI\Contracts\TransporterContract;
use Psr\Http\Message\StreamInterface;

it('no error when construct with no model', function () {
    $config = new OpenAIConfig();
    $config->apiKey = 'fakeapikey';
    $imageService = new OpenAIImage($config);
    expect(isset($imageService))->toBeTrue();
});

it('returns a stream response using generateStreamOfText()', function () {
    $response = new Response(
        200,
        [],
        [
            "data": [
                {
                  "url": "https://example.com/1234567",
                  "revisedPrompt": "A revised prompt"
                }
            ]
        ]
    );
    $transport = Mockery::mock(TransporterContract::class);
    $transport->allows([
        'requestStream' => $response,
    ]);

    $config = new OpenAIConfig();
    $config->client = new Client($transport);
    $chat = new OpenAIImage($config);

    $response = $chat->generateImage('this is the prompt image');
    expect($response)->toBeInstanceof(Image::class);
});

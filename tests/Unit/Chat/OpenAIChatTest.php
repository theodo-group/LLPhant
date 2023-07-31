<?php

namespace Tests\Unit\Chat;

use LLPhant\Chat\OpenAIChat;
use LLPhant\OpenAIConfig;

it('no error when construct with no model', function () {
    $config = new OpenAIConfig();
    $config->apiKey = 'fakeapikey';
    $chat = new OpenAIChat($config);
    expect(isset($chat))->toBeTrue();
});

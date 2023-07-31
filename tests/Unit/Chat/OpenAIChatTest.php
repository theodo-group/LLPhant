<?php

namespace Tests\Unit\Chat;

use LLPhant\Chat\OpenAIChat;
use LLPhant\Chat\OpenAIChatConfig;

it('no error when construct with no model', function () {
    $config = new OpenAIChatConfig();
    $config->apiKey = 'fakeapikey';
    $chat = new OpenAIChat($config);
    expect(isset($chat))->toBeTrue();
});

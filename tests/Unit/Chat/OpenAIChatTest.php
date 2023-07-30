<?php

namespace Tests\Unit\Chat;

use LLPhant\Chat\OpenAIChat;

it('no error when init with empty config', function () {
    $chat = new OpenAIChat();
    expect(isset($chat))->toBeTrue();
});

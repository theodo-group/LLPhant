<?php

namespace LLPhant\Chat;

use LLPhant\Chat\Enums\ChatRole;

class Message
{
    public ChatRole $role;

    public string $content;
}

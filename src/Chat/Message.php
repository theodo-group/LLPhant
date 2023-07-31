<?php

namespace LLPhant\Chat;

use LLPhant\Chat\Enums\ChatRole;

final class Message
{
    public ChatRole $role;

    public string $content;
}

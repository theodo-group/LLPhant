<?php

namespace LLPhant\Chat;

use LLPhant\Chat\Enums\ChatRole;

class Message
{
    public ChatRole $role;

    public string $content;

    public string $name;

    public static function system(string $content): self
    {
        $message = new self();
        $message->role = ChatRole::System;
        $message->content = $content;

        return $message;
    }

    public static function user(string $content): self
    {
        $message = new self();
        $message->role = ChatRole::User;
        $message->content = $content;

        return $message;
    }

    public static function assistant(string $content): self
    {
        $message = new self();
        $message->role = ChatRole::Assistant;
        $message->content = $content;

        return $message;
    }

    public static function functionResult(string $content, string $name): self
    {
        $message = new self();
        $message->role = ChatRole::Function;
        $message->content = $content;
        $message->name = $name;

        return $message;
    }

    public static function toolResult(string $content): self
    {
        $message = new self();
        $message->role = ChatRole::Tool;
        $message->content = $content;

        return $message;
    }
}

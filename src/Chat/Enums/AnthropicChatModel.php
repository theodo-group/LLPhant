<?php

namespace LLPhant\Chat\Enums;

enum AnthropicChatModel: string
{
    case Claude3Haiku = 'claude-3-haiku-20240307';
    case Claude35Sonnet = 'claude-3-5-sonnet-20240620';
    case Claude3Sonnet = 'claude-3-sonnet-20240229';
    case Claude3Opus = 'claude-3-opus-20240229';
}

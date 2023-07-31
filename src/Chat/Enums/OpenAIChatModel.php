<?php

namespace LLPhant\Chat\Enums;

enum OpenAIChatModel
{
    case Gpt35Turbo;
    case Gpt4;

    public function getModelName(): string
    {
        return match ($this) {
            OpenAIChatModel::Gpt35Turbo => 'gpt-3.5-turbo',
            OpenAIChatModel::Gpt4 => 'gpt-4',
        };
    }
}

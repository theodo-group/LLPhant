<?php

namespace LLPhant\Chat\Enums;

enum MistralAIChatModel
{
    case tiny;
    case small;
    case medium;
    case large;

    public function getModelName(): string
    {
        return match ($this) {
            MistralAIChatModel::tiny => 'mistral-tiny',
            MistralAIChatModel::small => 'mistral-small',
            MistralAIChatModel::medium => 'mistral-medium',
            MistralAIChatModel::large => 'mistral-large',
        };
    }
}

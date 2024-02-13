<?php

namespace LLPhant\Chat\Enums;

enum MistralAIChatModel
{
    case tiny;
    case small;
    case medium;

    public function getModelName(): string
    {
        return match ($this) {
            MistralAIChatModel::tiny => 'mistral-tiny',
            MistralAIChatModel::small => 'mistral-small',
            MistralAIChatModel::medium => 'mistral-medium',
        };
    }
}

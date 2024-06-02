<?php

namespace LLPhant\Image\Enums;

enum OpenAIImageModel
{
    case DallE3;

    public function getModelName(): string
    {
        return match ($this) {
            OpenAIImageModel::DallE3 => 'dall-e-3',
        };
    }
}

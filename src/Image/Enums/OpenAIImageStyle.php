<?php

namespace LLPhant\Image\Enums;

enum OpenAIImageStyle
{
    case Vivid;
    case Natural;

    public function getModelName(): string
    {
        return match ($this) {
            OpenAIImageStyle::Vivid => 'vivid',
            OpenAIImageStyle::Natural => 'natural',
        };
    }
}

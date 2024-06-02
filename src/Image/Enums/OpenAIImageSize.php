<?php

namespace LLPhant\Image\Enums;

enum OpenAIImageSize
{
    case size_1024x1024;
    case size_1792x1024;
    case size_1024x1792;

    public function getSize(): string
    {
        return match ($this) {
            OpenAIImageSize::size_1024x1024 => '1024x1024',
            OpenAIImageSize::size_1792x1024 => '1792x1024',
            OpenAIImageSize::size_1024x1792 => '1024x1792',
        };
    }
}

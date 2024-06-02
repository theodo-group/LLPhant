<?php

namespace LLPhant\Image;

use LLPhant\Image\Enums\OpenAIImageStyle;

interface ImageInterface
{
    public function generateImage(string $prompt, OpenAIImageStyle $style): Image;
}

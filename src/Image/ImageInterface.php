<?php

namespace LLPhant\Image;

interface ImageInterface
{
    public function generateImage(string $prompt): Image;
}

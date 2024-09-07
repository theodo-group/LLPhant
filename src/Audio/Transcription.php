<?php

namespace LLPhant\Audio;

class Transcription
{
    public function __construct(public readonly string $text, public readonly ?string $language, public readonly ?float $durationInSeconds)
    {
    }
}

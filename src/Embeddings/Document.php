<?php

declare(strict_types=1);

namespace LLPhant\Embeddings;

class Document
{
    public string $content;

    public ?string $formattedContent = null;

    /** @var float[]|null */
    public ?array $embedding = null;

    public ?string $sourceType = null;

    public ?string $sourceName = null;

    public ?string $hash = null;
}

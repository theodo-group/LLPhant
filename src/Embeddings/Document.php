<?php

declare(strict_types=1);

namespace LLPhant\Embeddings;

final class Document
{
    public string $content;

    public ?string $formattedContent = null;

    public ?string $sourceType = null;

    public ?string $sourceName = null;

    public ?string $hash = null;

    public ?string $id = null;
}

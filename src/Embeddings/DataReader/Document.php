<?php

namespace LLPhant\Embeddings\DataReader;

final class Document
{
    public string $content;

    public ?string $sourceType = null;

    public ?string $sourceName = null;

    public ?string $hash = null;

    public ?string $id = null;
}

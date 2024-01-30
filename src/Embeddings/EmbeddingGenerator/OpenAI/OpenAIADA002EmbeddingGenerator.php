<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator\OpenAI;

use Exception;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\OpenAIConfig;
use OpenAI;
use OpenAI\Client;
use function getenv;
use function str_replace;

final class OpenAIEmbeddingGenerator extends AbstractOpenAIEmbeddingGenerator
{
    public const OPENAI_EMBEDDING_LENGTH = 1536;

    public string $modelName = 'text-embedding-ada-002';

    public function getEmbeddingLength(): int
    {
        // TODO: Implement getEmbeddingLength() method.
    }

    public function getModelName(): string
    {
        // TODO: Implement getModelName() method.
    }
}

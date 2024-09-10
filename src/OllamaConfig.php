<?php

declare(strict_types=1);

namespace LLPhant;

/**
 * @see https://github.com/ollama/ollama/blob/main/docs/api.md#generate-a-completion
 *
 * @phpstan-type ModelFileOptions array{
 *     mirostat?: int|null,
 *     mirostat_eta?: float|null,
 *     mirostat_tau?: float|null,
 *     num_ctx?: int|null,
 *     repeat_last_n?: int|null,
 *     repeat_penalty?: float|null,
 *     temperature?: float|null,
 *     seed?: int|null,
 *     stop?: string|array<string>|null,
 *     tfs_z?: float|null,
 *     num_predict?: int|null,
 *     top_k?: int|null,
 *     top_p?: float|null,
 *     min_p?: float|null,
 * }
 * @phpstan-type ModelOptions array{
 *     format?: string|null,
 *     options?: ModelFileOptions|null,
 *     template?: string|null,
 *     context?: array<int>|null,
 *     raw?: bool|null,
 *     keep_alive?: string|null,
 * }
 */
class OllamaConfig
{
    /**
     * @param  ModelOptions  $modelOptions
     */
    public function __construct(
        public string $url = 'http://localhost:11434/api/',
        public string $model = 'llama3.1',
        public array $modelOptions = [],
    ) {
    }
}

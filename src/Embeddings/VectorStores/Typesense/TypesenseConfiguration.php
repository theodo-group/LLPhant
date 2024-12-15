<?php

namespace LLPhant\Embeddings\VectorStores\Typesense;

class TypesenseConfiguration
{
    /**
     * @param  string[]  $nodes
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly array $nodes,
    ) {
    }

    /**
     * @return array{api_key: string, nodes: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'api_key' => $this->apiKey,
            'nodes' => $this->splitNodes(),
        ];
    }

    /**
     * @return array<array<string, string>>
     */
    private function splitNodes(): array
    {
        return \array_map($this->parseCustomUrl(...), $this->nodes);
    }

    /**
     * @return array{protocol: string, host: string, port: string}
     */
    private function parseCustomUrl(string $url): array
    {
        $parsedUrl = parse_url($url);

        return [
            'protocol' => $parsedUrl['scheme'] ?? '',
            'host' => $parsedUrl['host'] ?? '',
            'port' => isset($parsedUrl['port']) ? (string) $parsedUrl['port'] : '',
        ];
    }
}

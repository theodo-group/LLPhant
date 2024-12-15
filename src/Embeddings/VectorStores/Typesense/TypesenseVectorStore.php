<?php

namespace LLPhant\Embeddings\VectorStores\Typesense;

use Exception;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use Typesense\Client as TypesenseClient;

class TypesenseVectorStore extends VectorStoreBase
{
    final public const TYPESENSE_VECTOR_NAME = 'embedding';

    public TypesenseClient $client;

    /**
     * @param  string[]  $nodes
     *
     * @throws \Typesense\Exceptions\ConfigError
     */
    public function __construct(
        string $apiKey,
        array $nodes,
        private readonly string $collectionName,
        private readonly string $vectorName = self::TYPESENSE_VECTOR_NAME,
    ) {
        $configuration = new TypesenseConfiguration($apiKey, $nodes);
        $this->client = new TypesenseClient($configuration->toArray());
    }

    /**
     * @param  int  $embeddingLength  this depends on the embedding generator you use
     */
    public function createCollectionIfDoesNotExist(string $name, int $embeddingLength): bool
    {
        if (! $this->client->collections[$name]->exists()) {
            $this->createCollection($name, $embeddingLength);

            return false;
        }

        return true;
    }

    /**
     * @param  int  $embeddingLength  this depends on the embedding generator you use
     */
    public function createCollection(string $name, int $embeddingLength): void
    {
        $this->client->collections->create([
            'name' => $name,
            'fields' => [
                [
                    'name' => $this->vectorName,
                    'type' => 'float[]',
                    'num_dim' => $embeddingLength,
                ],
                [
                    'name' => 'id',
                    'type' => 'string',
                ],
                [
                    'name' => 'content',
                    'type' => 'string',
                ],
                [
                    'name' => 'hash',
                    'type' => 'string',
                ],
                [
                    'name' => 'sourceName',
                    'type' => 'string',
                ],
                [
                    'name' => 'sourceType',
                    'type' => 'string',
                ],
                [
                    'name' => 'chunkNumber',
                    'type' => 'int32',
                ],
            ],
        ]);
    }

    public function addDocument(Document $document): void
    {
        $this->client->collections[$this->collectionName]
            ->documents->upsert($this->createPointFromDocument($document));
    }

    public function addDocuments(array $documents): void
    {
        foreach ($documents as $document) {
            $this->addDocument($document);
        }
    }

    /**
     * @param  float[]  $embedding
     * @return array|mixed[]
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        $vectorQuery = $this->vectorName.':(['.implode(',', $embedding).'], k:'.$k.')';

        $response = $this->client->multiSearch->perform([
            'searches' => [
                [
                    'collection' => $this->collectionName,
                    'q' => '*',
                    'vector_query' => $vectorQuery,
                    'exclude_fields' => $this->vectorName,
                ],
            ],
        ], $additionalArguments);

        $results = $response['results'];

        if (! \is_array($results)) {
            return [];
        }

        $documents = [];

        foreach ($results as $result) {
            $hits = $result['hits'];
            if (! \is_array($hits)) {
                return $documents;
            }
            foreach ($hits as $onePoint) {
                $document = new Document();
                $document->content = $onePoint['document']['content'];
                $document->hash = $onePoint['document']['hash'];
                $document->sourceType = $onePoint['document']['sourceType'];
                $document->sourceName = $onePoint['document']['sourceName'];
                $document->chunkNumber = $onePoint['document']['chunkNumber'];
                $documents[] = $document;
            }
        }

        return $documents;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function createPointFromDocument(Document $document): array
    {
        if ($document->embedding === null) {
            throw new Exception('Impossible to save a document without its vectors. You need to call an embeddingGenerator: $embededDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);');
        }

        return [
            'id' => $this->getId($document),
            $this->vectorName => $document->embedding,
            'content' => $document->content,
            'hash' => $document->hash,
            'sourceName' => $document->sourceName,
            'sourceType' => $document->sourceType,
            'chunkNumber' => $document->chunkNumber,
        ];
    }

    protected function getId(Document $document): string
    {
        return \hash('sha256', $document->content.DocumentUtils::getUniqueId($document));
    }
}

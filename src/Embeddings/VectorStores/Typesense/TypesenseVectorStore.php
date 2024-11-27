<?php

namespace LLPhant\Embeddings\VectorStores\Qdrant;

use Exception;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;
use Typesense\Client as Typesense;


class QdrantVectorStore extends VectorStoreBase
{
    final public const TYPESENSE_VECTOR_NAME = 'embedding';

    public Typesense $client;

    /**
     * @param  array<string, mixed>  $config 
     * @param  string  $collectionName
     * @param  string|null  $vectorName
     * @see Typesense\Lib\Configuration
     */
    public function __construct(
        array $config,
        private string $collectionName,
        private ?string $vectorName = self::TYPESENSE_VECTOR_NAME,
    ) {
        $this->client = new Typesense($config);
    }

    public function setClient(Typesense $client): void
    {
        $this->client = $client;
    }

    public function setVectorName(?string $vectorName): void
    {
        $this->vectorName = $vectorName;
    }

    /**
     * @param  int  $embeddingLength  this depends on the embedding generator you use
     */
    public function createCollectionIfDoesNotExist(string $name, int $embeddingLength): bool
    {
        try {
            if (! $this->client->collections[$name]->exists()) { 
                throw new \Exception('Collection does not exist');
            }

            return true;
        } catch (\Exception) {
            $this->createCollection($name, $embeddingLength);

            return false;
        }
    }

    /**
     * @param  int  $embeddingLength  this depends on the embedding generator you use
     */
    public function createCollection(string $name, int $embeddingLength): array
    {
        return $this->client->collections->create([
            'name' => $name,
            "fields" => [
                [
                  "name" => $this->vectorName,
                  "type" => "float[]",
                  "num_dim" => $embeddingLength
                ],
                [
                    "name" => "id",
                    "type" => "string",
                ],
                [
                    "name" => "content",
                    "type" => "string"
                ],
                [
                    "name" => "hash",
                    "type" => "string"
                ],
                [
                    "name" => "sourceName",
                    "type" => "string"
                ],
                [
                    "name" => "sourceType",
                    "type" => "string"
                ],
                [
                    "name" => "chunkNumber",
                    "type" => "int32"
                ],
            ],
        ]);
    }

    public function addDocument(Document $document): void
    {
        $point = $this->createPointFromDocument($document);

        $this->client->collections[$this->collectionName]
            ->documents->create($point);
    }

    public function addDocuments(array $documents): void
    {
        if ($documents === []) {
            return;
        }

        $points = [];
        foreach ($documents as $document) {
            $points[] = $this->createPointFromDocument($document);
        }

        $this->client->collections[$this->collectionName]
            ->documents->upsert($points);
    }

    /**
     * @param  float[]  $embedding
     * @param  array  $additionalArguments
     * @return array|mixed[]
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        $vector_query = $this->vectorName . ':([' . implode(',', $embedding) . '], k:' . $k . ')';

        $response = $this->client->multiSearch->perform([
            'searches' => [
                [
                    'collection' => $this->collectionName,
                    'q' => "*",
                    "vector_query" => $vector_query,
                    'exclude_fields' => $this->vectorName,
                ],
            ],
        ], $additionalArguments);

        $results = $response['hits'];

        if ((is_countable($results) ? count($results) : 0) === 0) {
            return [];
        }

        $documents = [];
        foreach ($results as $onePoint) {
            $document = new Document();
            $document->content = $onePoint['document']['content'];
            $document->hash = $onePoint['document']['hash'];
            $document->sourceType = $onePoint['document']['sourceType'];
            $document->sourceName = $onePoint['document']['sourceName'];
            $document->chunkNumber = $onePoint['document']['chunkNumber'];
            $documents[] = $document;
        }

        return $documents;
    }

    /**
     * @throws Exception
     */
    private function createPointFromDocument(Document $document): array
    {
        if (! is_array($document->embedding)) {
            throw new Exception('Impossible to save a document without its vectors. You need to call an embeddingGenerator: $embededDocuments = $embeddingGenerator->embedDocuments($formattedDocuments);');
        }

        $id = DocumentUtils::formatUUIDFromUniqueId(DocumentUtils::getUniqueId($document));
        return [
            'id' => $id,
            $this->vectorName => $document->embedding,
            'content' => $document->content,
            'hash' => $document->hash,
            'sourceName' => $document->sourceName,
            'sourceType' => $document->sourceType,
            'chunkNumber' => $document->chunkNumber,
        ];
    }
}
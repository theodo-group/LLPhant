<?php

namespace LLPhant\Embeddings\VectorStores\Milvus;

use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;

class MilvusVectorStore extends VectorStoreBase
{
    final public const MILVUS_COLLECTION_NAME = 'llphant';

    public bool $collectionExists = false;

    public function __construct(
        public MilvusClient $client,
        public string $collectionName = self::MILVUS_COLLECTION_NAME
    ) {
    }

    public function addDocument(Document $document): void
    {
        $this->addDocuments([$document]);
    }

    public function addDocuments(array $documents, int $numberOfDocumentsPerRequest = 0): void
    {
        if ($documents === []) {
            return;
        }
        $embeddingDimension = count((array) $documents[0]->embedding);
        $this->createCollectionIfNotExist($embeddingDimension);
        $response = $this->client->insertData(
            $this->collectionName,
            array_map(
                fn (Document $document): array => [
                    'embedding' => $document->embedding,
                    'content' => $document->content,
                    'formattedContent' => $document->formattedContent,
                    'sourceType' => $document->sourceType,
                    'sourceName' => $document->sourceName,
                    'hash' => $document->hash,
                    'chunkNumber' => $document->chunkNumber,
                ],
                $documents
            )
        );
        if ($response['code'] !== 200) {
            throw new \Exception('Error while inserting data');
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param  array{filter?: string}  $additionalArguments
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        /** @var array{code: int, data: array{content: string, formattedContent: string, sourceType: string, sourceName: string, hash: string, embedding: float[], chunkNumber: int}[]} $response */
        $response = $this->client->searchVector(
            $this->collectionName,
            $embedding,
            $k,
            array_key_exists('filter', $additionalArguments) ? $additionalArguments['filter'] : '',
            ['id', 'content', 'formattedContent', 'sourceType', 'sourceName', 'hash', 'chunkNumber', 'embedding']
        );
        if ($response['code'] !== 200) {
            throw new \Exception('Error while searching vector');
        }

        return DocumentUtils::createDocumentsFromArray($response['data']);
    }

    private function createCollectionIfNotExist(int $dimension): void
    {
        if ($this->collectionExists) {
            return;
        }
        // It returns 200 if the collection exist AND is the same
        $response = $this->client->createCollection(
            collectionName: $this->collectionName,
            dimension: $dimension,
            metricType: 'COSINE',
            primaryField: 'id',
            vectorField: 'embedding'
        );
        if ($response['code'] !== 200) {
            throw new \Exception('Error while creating collection');
        }
        $this->collectionExists = true;
    }
}

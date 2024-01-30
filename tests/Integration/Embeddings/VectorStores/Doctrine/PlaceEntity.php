<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\VectorStores\Doctrine;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineEmbeddingEntityBase;
use LLPhant\Embeddings\VectorStores\Doctrine\VectorType;

#[Entity]
#[Table(name: 'test_place')]
class PlaceEntity extends DoctrineEmbeddingEntityBase
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public ?string $type;

    #[ORM\Column(type: VectorType::VECTOR, length: 3072)]
    public ?array $embedding;
}

<?php

namespace Tests\Integration\VectorStores\Doctrine;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineEmbeddingEntityBase;

#[Entity]
#[Table(name: 'test_place')]
class PlaceEntity extends DoctrineEmbeddingEntityBase
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public ?string $type;
}

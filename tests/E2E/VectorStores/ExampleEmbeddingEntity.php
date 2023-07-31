<?php

namespace Tests\E2E\VectorStores;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use LLPhant\VectorStores\EmbeddingEntityBase;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity]
#[Table(name: 'embeddings', schema: 'public')]
class ExampleEmbeddingEntity extends EmbeddingEntityBase
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    public string $id;

    #[ORM\Column(type: Types::TEXT)]
    public string $data;

    #[ORM\Column(type: Types::STRING)]
    public string $type;

    public function getId(): string
    {
        return $this->id;
    }
}

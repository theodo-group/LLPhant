<?php

namespace LLPhant\VectorStores;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'embeddings', schema: 'public')]
class ExampleEmbeddingEntity extends EmbeddingEntityBase
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public int $id;

    #[ORM\Column(type: Types::TEXT)]
    public string $data;

    #[ORM\Column(type: Types::STRING)]
    public string $type;

    public function getId(): int
    {
        return $this->id;
    }
}

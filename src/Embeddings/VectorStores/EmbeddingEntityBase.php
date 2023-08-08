<?php

namespace LLPhant\Embeddings\VectorStores;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

abstract class EmbeddingEntityBase
{
    #[ORM\Column(type: Types::TEXT)]
    public string $embedding;

    abstract public function getId(): mixed;
}

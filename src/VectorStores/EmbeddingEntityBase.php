<?php

namespace LLPhant\VectorStores;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

class EmbeddingEntityBase
{
    #[ORM\Column(type: Types::TEXT)]
    public string $embedding;
}

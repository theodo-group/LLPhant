<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LLPhant\Embeddings\Document;

class DoctrineEmbeddingEntityBase extends Document
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public int $id;

    #[ORM\Column(type: Types::TEXT)]
    public string $pgembedding;

    #[ORM\Column(type: Types::TEXT)]
    public string $content;

    #[ORM\Column(type: Types::TEXT)]
    public string $sourceType = 'manual';

    #[ORM\Column(type: Types::TEXT)]
    public string $sourceName = 'manual';

    public function getId(): int
    {
        return $this->id;
    }
}

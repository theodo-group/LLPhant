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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $sourceType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $sourceName = null;

    public function getId(): int
    {
        return $this->id;
    }
}

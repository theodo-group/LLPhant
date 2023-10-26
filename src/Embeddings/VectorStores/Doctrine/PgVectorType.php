<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PgVectorType extends Type
{
    const VECTOR = 'vector';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (array_key_exists('length', $column)) {
            return sprintf('vector(%d)', $column['length']);
        }

        return 'vector';
    }

    /**
     * @param float[] $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return '['.implode(',', $value).']';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        return explode(',', trim($value, '[]'));
    }

    public function getName(): string
    {
        return self::VECTOR;
    }
}

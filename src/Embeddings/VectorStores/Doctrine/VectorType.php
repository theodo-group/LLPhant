<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class VectorType extends Type
{
    final public const VECTOR = 'vector';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        $vectorSize = $fieldDeclaration['length'];
        if (is_int($vectorSize)) {
            return sprintf('vector(%d)', $vectorSize);
        }

        return 'vector';
    }

    /**
     * @return float[]
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return null;
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        if (! is_string($value)) {
            throw Exception::notSupported('VECTORs are stored as string in pgvector, unexpected value.');
        }

        $vectorStringArray = explode(',', str_replace(['[', ']'], '', $value));
        $floats = [];
        foreach ($vectorStringArray as $stringVector) {
            $floats[] = (float) $stringVector;
        }

        return $floats;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (! is_array($value)) {
            return null;
        }
        if ($value === []) {
            return null;
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    public function getName(): string
    {
        return self::VECTOR;
    }
}

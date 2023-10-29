<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;

class VectorType extends Type
{
    final public const VECTOR = 'vector';

    /**
     * @param  mixed[]  $fieldDeclaration
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        if (! $platform instanceof PostgreSQLPlatform) {
            throw Exception::notSupported('VECTORs not supported by Platform.');
        }

        if (! isset($fieldDeclaration['length'])) {
            throw Exception::notSupported('VECTORs must have a length.');
        }

        if ($fieldDeclaration['length'] < 1) {
            throw Exception::notSupported('VECTORs must have a length greater than 0.');
        }

        if (! is_int($fieldDeclaration['length'])) {
            throw Exception::notSupported('VECTORs must have a length that is an integer.');
        }

        return sprintf('vector(%d)', $fieldDeclaration['length']);
    }

    /**
     * @return float[]
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        if ($value === null) {
            return [];
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        if (! is_string($value)) {
            throw Exception::notSupported('Error while converting VECTORs to PHP value.');
        }

        $convertedValue = explode(',', $value);
        $floatArray = [];
        foreach ($convertedValue as $singleConvertedValue) {
            $floatArray[] = (float) $singleConvertedValue;
        }

        return $floatArray;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (! is_array($value)) {
            throw Exception::notSupported('VECTORs must be an array.');
        }

        return VectorUtils::getVectorAsString($value);
    }

    public function getName(): string
    {
        return self::VECTOR;
    }
}

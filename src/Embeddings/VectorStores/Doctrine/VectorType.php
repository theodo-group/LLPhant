<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

class VectorType extends Type
{
    public const VECTOR = 'vector';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if(!$platform instanceof PostgreSQLPlatform) {
            throw Exception::notSupported('VECTORs not supported by Platform.');
        }

        return sprintf('vector(%d)', $fieldDeclaration['length']);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return [];
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        return explode(',', $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return VectorUtils::getVectorAsString($value);
    }

    public function getName()
    {
        return self::VECTOR;
    }
}

<?php

declare(strict_types=1);

namespace LLPhant;

use JsonException;
use LLPhant\Exception\FormatException;

class Utility
{
    /**
     * Decode a JSON string into an array
     *
     * @return mixed[]
     */
    public static function decodeJson(string $json): array
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new FormatException(sprintf(
                'JSON error decoding: %s',
                $e->getMessage()
            ), $e->getCode(), $e);
        }
    }
}

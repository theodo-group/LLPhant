<?php

namespace LLPhant\Utils;

class StringParser
{
    /**
     * @return string[]
     */
    public static function extractURL(string $string): array
    {
        preg_match_all('/\bhttps?:\/\/\S+\b/', $string, $matches);

        return $matches[0];
    }
}

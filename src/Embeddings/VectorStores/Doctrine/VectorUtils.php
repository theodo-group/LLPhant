<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

class VectorUtils
{
    /**
     * @param  float[]  $vector
     */
    public static function getVectorAsString(array $vector): string
    {
        if ($vector === []) {
            return '';
        }

        return '['.implode(',', $vector).']';
    }
}

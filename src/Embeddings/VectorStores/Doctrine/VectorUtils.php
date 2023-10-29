<?php

namespace LLPhant\Embeddings\VectorStores\Doctrine;

class VectorUtils
{
    /**
     * @param float[] $vector
     * @return string
     */
    static public function getVectorAsString(array $vector): string
    {
        if (count($vector) === 0) {
            return "";
        }

        return '[' . implode(',', $vector) . ']';
    }
}

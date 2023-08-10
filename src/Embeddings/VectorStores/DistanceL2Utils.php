<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\VectorStores;

use InvalidArgumentException;

class DistanceL2Utils
{
    /**
     * @param  float[]  $vector1
     * @param  float[]  $vector2
     */
    public static function euclideanDistanceL2(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            throw new InvalidArgumentException('Arrays must have the same length.');
        }

        $sum = 0.0;
        foreach ($vector1 as $i => $singleVector1) {
            $sum += ($singleVector1 - $vector2[$i]) ** 2;
        }

        return sqrt($sum);
    }
}

<?php

namespace LLPhant\Embeddings\Distances;

class EuclideanDistanceL2 implements Distance
{
    /**
     * {@inheritDoc}
     */
    public function measure(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            throw new \InvalidArgumentException('Arrays must have the same length.');
        }

        $sum = 0.0;
        foreach ($vector1 as $i => $singleVector1) {
            $sum += ($singleVector1 - $vector2[$i]) ** 2;
        }

        return sqrt($sum);
    }
}

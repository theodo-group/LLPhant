<?php

namespace LLPhant\Embeddings\Distances;

class CosineDistance implements Distance
{
    /**
     * {@inheritDoc}
     */
    public function measure(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            throw new \InvalidArgumentException('Arrays must have the same length.');
        }

        // Calculate the dot product of the two vectors
        $dotProduct = array_sum(array_map(fn (float $a, float $b): float => $a * $b, $vector1, $vector2));

        // Calculate the magnitudes of each vector
        $magnitude1 = sqrt(array_sum(array_map(fn (float $a): float => $a * $a, $vector1)));

        $magnitude2 = sqrt(array_sum(array_map(fn (float $a): float => $a * $a, $vector2)));

        // Avoid division by zero
        if ($magnitude1 * $magnitude2 == 0) {
            return 0;
        }

        // Calculate the cosine distance
        return 1 - $dotProduct / ($magnitude1 * $magnitude2);
    }
}

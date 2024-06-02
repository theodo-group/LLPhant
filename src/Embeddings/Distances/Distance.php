<?php

namespace LLPhant\Embeddings\Distances;

interface Distance
{
    /**
     * @param  float[]  $vector1
     * @param  float[]  $vector2
     */
    public function measure(array $vector1, array $vector2): float;
}

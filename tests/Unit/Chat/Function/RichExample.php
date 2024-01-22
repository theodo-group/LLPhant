<?php

declare(strict_types=1);

namespace Tests\Unit\Chat\Function;

class RichExample
{
    /**
     * This is the description of the example function from the RichExample class.
     *
     * @param  string  $stringVar  This is the description of the stringVar parameter.
     * @param  int  $intVar  This is the description of the intVar parameter.
     * @param  float  $floatVar  This is the description of the floatVar parameter.
     * @param  bool  $boolVar  This is the description of the boolVar parameter.
     * @param  mixed[]  $arrayVar  This is the description of the arrayVar parameter.
     */
    public function example(string $stringVar, int $intVar, float $floatVar, bool $boolVar, array $arrayVar): void
    {
        var_dump($stringVar, $intVar, $floatVar, $boolVar, $arrayVar);
    }
}

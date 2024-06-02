<?php

namespace Tests\Unit\Embeddings\Distances;

use LLPhant\Embeddings\Distances\CosineDistance;

it('satisfies the coincidence axiom', function (array $v1) {
    $distance = new CosineDistance();
    expect($distance->measure($v1, $v1))->toBe(0.0);
})->with([
    [[0.88, 42.0, -99.1]],
    [[22.0]],
    [[0.0, 84.0]],
]);

it('is symmetric', function (array $v1, array $v2) {
    $distance = new CosineDistance();
    expect($distance->measure($v1, $v2))->toBe($distance->measure($v2, $v1));
})->with([
    [[0.5, 9.4], [1.3, -9.81]],
]);

<?php

declare(strict_types=1);

namespace Tests\Unit\Query\SemanticSearch;

use LLPhant\Query\SemanticSearch\PipeTransformer;
use LLPhant\Query\SemanticSearch\QueryTransformer;

function queryTransformer(string $color): QueryTransformer
{
    return new class($color) implements QueryTransformer
    {
        public function __construct(private readonly string $color)
        {
        }

        public function transformQuery(string $query): array
        {
            return [$query.' '.$this->color];
        }
    };
}

it('can pipe transformations', function () {
    $transformer = new PipeTransformer(queryTransformer('green'), queryTransformer('white'), queryTransformer('red'));
    $transformed = $transformer->transformQuery('sample');
    expect($transformed[0])->toBe('sample green white red');
});

it('can add transformers to pipe transformations', function () {
    $transformer = new PipeTransformer(queryTransformer('green'), queryTransformer('white'), queryTransformer('red'));
    $transformer->addTransformer(queryTransformer('blue'));
    $transformed = $transformer->transformQuery('sample');
    expect($transformed[0])->toBe('sample green white red blue');
});

<?php

declare(strict_types=1);

namespace Tests\Unit\Query\SemanticSearch;

use LLPhant\Embeddings\DocumentUtils;
use LLPhant\Query\SemanticSearch\PipeDocumentsTransformer;
use LLPhant\Query\SemanticSearch\RetrievedDocumentsTransformer;

function transformer(string $color): RetrievedDocumentsTransformer
{
    return new class($color) implements RetrievedDocumentsTransformer
    {
        public function __construct(private readonly string $color)
        {
        }

        public function transformDocuments(array $questions, array $retrievedDocs): array
        {
            foreach ($retrievedDocs as $retrievedDoc) {
                $retrievedDoc->content .= ' '.$this->color;
            }

            return $retrievedDocs;
        }
    };
}

it('can pipe transformations', function () {
    $transformer = new PipeDocumentsTransformer(transformer('green'), transformer('white'), transformer('red'));
    $transformed = $transformer->transformDocuments(['sample'], DocumentUtils::documents('one', 'two', 'three'));
    expect($transformed[0]->content)->toBe('one green white red')
        ->and($transformed[1]->content)->toBe('two green white red')
        ->and($transformed[2]->content)->toBe('three green white red');
});

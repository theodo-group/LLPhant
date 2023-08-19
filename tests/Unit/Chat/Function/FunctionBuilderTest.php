<?php

namespace Tests\Unit\Chat\Function;

use LLPhant\Chat\Function\FunctionBuilder;

it('creates a functionInfo instance from a random class method', function () {

    $functionInfo = FunctionBuilder::buildFunctionInfo(new RichExample(), 'example');

    expect($functionInfo->name)->toBe('example');
    expect($functionInfo->description)->toBe('This is the description of the example function from the RichExample class.');
    expect($functionInfo->parameters[0]->name)->toBe('stringVar');
    expect($functionInfo->parameters[0]->type)->toBe('string');
});

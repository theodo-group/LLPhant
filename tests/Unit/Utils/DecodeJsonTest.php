<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use LLPhant\Exception\FormatException;
use LLPhant\Utility;

it('decode JSON', function (string $input, array $output) {
    expect($output)->toBe(Utility::decodeJson($input));
})->with([
    ['{}', []],
    ['[]', []],
    ['{"foo":"bar"}', ['foo' => 'bar']],
    ['{"foo": {}}', ['foo' => []]],
    ['{"foo": ""}', ['foo' => '']],
    ['{"foo": null}', ['foo' => null]],
]);

it('decode JSON with error', function (string $input) {
    $result = Utility::decodeJson($input);
})->with([
    [''],
    ['{ "foo": '],
    ['{ "foo": "bar", }'],
])->throws(FormatException::class);

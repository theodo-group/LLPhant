<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use LLPhant\Render\StringParser;

it('extract URL for various edge cases', function () {
    // Edge case 1: Empty string
    expect([])->toBe(StringParser::extractURL(''));
    // Edge case 2: String with no URL
    expect([])->toBe(StringParser::extractURL('This is a test string.'));

    // Edge case 3: String with multiple URLs
    expect(
        ['https://example.com', 'http://another-example.com'])->toBe(
            StringParser::extractURL('Visit https://example.com and http://another-example.com')
        );
});

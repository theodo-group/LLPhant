<?php

declare(strict_types=1);

namespace Tests\Integration\Tool;

use LLPhant\Tool\WebPageTextGetter;

it('tests getWebPageText using real page', function () {
    $url = 'https://en.wikipedia.org/wiki/Chemical_element';

    $webPageTextGetter = new WebPageTextGetter(false);
    $webPageText = $webPageTextGetter->getWebPageText($url);

    $stringPosition = strpos($webPageText,
        'A chemical element is a chemical substance that cannot be broken down into other substances');
    expect($stringPosition)->toBeInt();
});

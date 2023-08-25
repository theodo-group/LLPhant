<?php

namespace Tests\Integration\DataReader;

use LLPhant\Embeddings\DataReader\TextFileDataReader;

it('read one specific file', function () {
    $filePath = __DIR__.'/TextFilesTestDirectory/hello.txt';
    $reader = new TextFileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toBe("hello test!\n");
});

it('can read pdf', function () {
    $filePath = __DIR__.'/TextFilesTestDirectory/data-pdf.pdf';
    $reader = new TextFileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toBe('This data is from a pdf');
});

it('can read pdf and texts ', function () {
    $filePath = __DIR__.'/TextFilesTestDirectory/';
    $reader = new TextFileDataReader($filePath);
    $documents = $reader->getDocuments();

    $foundPDF = false;
    $foundText = false;
    foreach ($documents as $document) {
        if ($document->content === 'This data is from a pdf') {
            $foundPDF = true;
        }
        if ($document->content === "hello test!\n") {
            $foundText = true;
        }
    }

    expect($foundPDF)->toBeTrue();
    expect($foundText)->toBeTrue();
});

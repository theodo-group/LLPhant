<?php

declare(strict_types=1);

namespace Tests\Unit\Embeddings\DataReader;

use LLPhant\Embeddings\DataReader\FileDataReader;

it('read one specific file', function () {
    $filePath = __DIR__.'/FilesTestDirectory/hello.txt';
    $reader = new FileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toBe("hello test!\n");
});

it('can read pdf', function () {
    $filePath = __DIR__.'/FilesTestDirectory/data-pdf.pdf';
    $reader = new FileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toBe('This data is from a pdf');
});

it('can read docx', function () {
    $filePath = __DIR__.'/FilesTestDirectory/data.docx';
    $reader = new FileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toBe('This data is from a docx');
});

it('can read pdf and texts ', function () {
    $filePath = __DIR__.'/FilesTestDirectory/';
    $reader = new FileDataReader($filePath);
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

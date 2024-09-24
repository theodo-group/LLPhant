<?php

declare(strict_types=1);

namespace Tests\Unit\Embeddings\DataReader;

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\Document;

it('can read various types of documents', function (string $docName, string $startingContent) {
    $filePath = __DIR__.'/FilesTestDirectory/'.$docName;
    $reader = new FileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toStartWith($startingContent);
})->with([
    ['hello.txt', "hello test!\n"],
    ['data.docx', 'This data is from a docx'],
    ['document-with-text-breaks.docx', 'Sample document with text breaks'],
    ['simple_document_with_links.docx', 'This is a doc with links'],
    ['data-pdf.pdf', 'This data is from a pdf'],
    ['powerpoint.pptx', 'Slide 1'],
]);

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

it('can filter files based on extensions', function () {
    $filePath = __DIR__.'/FilesTestDirectory/';
    $reader = new FileDataReader($filePath, Document::class, ['docx']);
    $documents = $reader->getDocuments();

    expect($documents)->toHaveCount(3);
});

it('can read sub-directories', function () {
    $filePath = __DIR__.'/FilesTestDirectory/';
    $reader = new FileDataReader($filePath, Document::class, ['txt']);
    $documents = $reader->getDocuments();

    $contents = array_map(fn ($doc) => $doc->content, $documents);

    expect($contents)->toContain("hello test!\n", "hello test2!\n", "hello test3!\n");
});

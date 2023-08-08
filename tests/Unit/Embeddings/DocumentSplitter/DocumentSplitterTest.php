<?php

use LLPhant\Embeddings\DataReader\Document;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;

it('splits a document by max length without separator', function () {
    $document = new Document();
    $document->content = 'This is a test';
    $result = DocumentSplitter::splitDocument($document, 3);
    expect($result[0]->content)->toBe('This');
    expect($result[1]->content)->toBe('is');
});

it('splits a document by max length with separator', function () {
    $document = new Document();
    $document->content = 'This-is-a-test';
    $result = DocumentSplitter::splitDocument($document, 11, '-');
    expect($result[0]->content)->toBe('This-is-a');
    expect($result[1]->content)->toBe('test');
});

it('returns the whole document if max length is greater than content', function () {
    $document = new Document();
    $document->content = 'This is a test';
    $result = DocumentSplitter::splitDocument($document, 50);
    expect($result[0]->content)->toBe('This is a test');
});

it('splits multiple documents', function () {
    $document1 = new Document();
    $document1->content = 'This is a test';
    $document2 = new Document();
    $document2->content = 'Hello World';
    $result = DocumentSplitter::splitDocuments([$document1, $document2], 5);
    expect($result[0]->content)->toBe('This');
    expect($result[1]->content)->toBe('is a');
    expect($result[2]->content)->toBe('test');
    expect($result[3]->content)->toBe('Hello');
});

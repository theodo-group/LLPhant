<?php

declare(strict_types=1);

namespace Tests\Unit\Embeddings\DocumentSplitter;

use LLPhant\Embeddings\Document;
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
    $document1->content = 'This is a test.';
    $document2 = new Document();
    $document2->content = 'Hello World!';
    $result = DocumentSplitter::splitDocuments([$document1, $document2], 5);
    expect($result[0]->content)->toBe('This is a test');
    expect($result[1]->content)->toBe('Hello World!');
});

it('splits texts with \n in it', function () {
    $document1 = new Document();
    $document1->content = 'Burritos are cool


France (French: [fʁɑ̃s] Listen), officially the French Republic (French: République française [ʁepyblik fʁɑ̃sɛz]),
[14] is a country located primarily in Western Europe.
It also includes overseas regions and territories in the Americas and the Atlantic,
Pacific and Indian Oceans,[XII] giving it one of the largest discontiguous exclusive economic zones in the world.


The house is on fire';
    $result = DocumentSplitter::splitDocument($document1, 100, "\n");
    expect($result[0]->content)->toBe('Burritos are cool');
});

<?php

namespace Tests\Integration\DataReader;

use LLPhant\Embeddings\DataReader\TextFileDataReader;

it('read some text files in a directory', function () {
    $filePath = __DIR__.'/TextFilesTestDirectory';
    $reader = new TextFileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toBe("hello test2!\n");
});

it('read one specific file', function () {
    $filePath = __DIR__.'/TextFilesTestDirectory/hello.txt';
    $reader = new TextFileDataReader($filePath);
    $documents = $reader->getDocuments();

    expect($documents[0]->content)->toBe("hello test!\n");
});

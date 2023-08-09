<?php

namespace LLPhant\Embeddings\DataReader;

use LLPhant\Embeddings\Document;

final class TextFileDataReader implements DataReader
{
    public string $sourceType = 'files';

    /**
     * @template T of Document
     *
     * @param  class-string<T>  $documentClassName
     */
    public function __construct(public string $filePath, public readonly string $documentClassName = Document::class)
    {
    }

    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        if (! file_exists($this->filePath)) {
            return [];
        }

        // If it's a directory
        if (is_dir($this->filePath)) {
            $documents = [];
            // Open the directory
            if ($handle = opendir($this->filePath)) {
                // Read the directory contents
                while (($entry = readdir($handle)) !== false) {
                    if ($entry != '.' && $entry != '..' && is_file($this->filePath.'/'.$entry)) {
                        $content = file_get_contents($this->filePath.'/'.$entry);
                        if ($content !== false) {
                            $document = new $this->documentClassName();
                            $document->content = $content;
                            $document->sourceType = $this->sourceType;
                            $document->hash = md5($content);
                            $document->sourceName = $entry;
                            $documents[] = $document;
                        }
                    }
                }

                // Close the directory
                closedir($handle);
            }

            return $documents;
        }
        // If it's a file
        $content = file_get_contents($this->filePath);
        if ($content === false) {
            return [];
        }
        $document = new $this->documentClassName();
        $document->content = $content;
        $document->sourceType = $this->sourceType;
        $document->hash = md5($content);
        $document->sourceName = $this->filePath;

        return [$document];
    }
}

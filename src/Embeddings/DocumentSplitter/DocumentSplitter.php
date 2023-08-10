<?php

namespace LLPhant\Embeddings\DocumentSplitter;

use LLPhant\Embeddings\Document;

final class DocumentSplitter
{
    /**
     * @return Document[]
     */
    public static function splitDocument(Document $document, int $maxLength = 1000, string $separator = ' '): array
    {
        $text = $document->content;
        if (empty($text)) {
            return [];
        }
        if ($maxLength <= 0) {
            return [];
        }

        if ($separator === '') {
            return [];
        }

        if (strlen($text) <= $maxLength) {
            return [$document];
        }

        $chunks = [];
        $words = explode($separator, $text);
        $currentChunk = '';

        foreach ($words as $word) {
            if (strlen($currentChunk.$separator.$word) <= $maxLength || empty($currentChunk)) {
                if (empty($currentChunk)) {
                    $currentChunk = $word;
                } else {
                    $currentChunk .= $separator.$word;
                }
            } else {
                $chunks[] = $currentChunk;
                $currentChunk = $word;
            }
        }

        if (! empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }
        $splittedDocuments = [];
        foreach ($chunks as $chunk) {
            $className = $document::class;
            $newDocument = new $className();
            $newDocument->content = $chunk;
            $newDocument->hash = md5($chunk);
            $newDocument->sourceType = $document->sourceType;
            $newDocument->sourceName = $document->sourceName;
            $splittedDocuments[] = $newDocument;
        }

        return $splittedDocuments;
    }

    /**
     * @param  Document[]  $documents
     * @return Document[]
     */
    public static function splitDocuments(array $documents, int $maxLength = 1000, string $separator = '.'): array
    {
        $splittedDocuments = [];
        foreach ($documents as $document) {
            $splittedDocuments = array_merge($splittedDocuments, DocumentSplitter::splitDocument($document, $maxLength, $separator));
        }

        return $splittedDocuments;
    }
}

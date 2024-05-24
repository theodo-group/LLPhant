<?php

namespace LLPhant\Embeddings\DataReader;

use Exception;
use LLPhant\Embeddings\Document;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

final class FileDataReader implements DataReader
{
    public string $sourceType = 'files';

    /**
     * @template T of Document
     *
     * @param  class-string<T>  $documentClassName
     * @param string[] $extensions
     */
    public function __construct(public string $filePath, public readonly string $documentClassName = Document::class, private readonly array $extensions = [])
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

        return $this->getDocumentsFrom($this->filePath);
    }

    /**
     * @return Document[]
     * @throws Exception
     */
    private function getDocumentsFrom(string $path): array
    {
        // If it's a directory
        if (is_dir($path)) {
            return $this->getContentFromDirectory($path);
        }
        // If it's a file
        $content = $this->getContentFromFile($path);
        if ($content === false) {
            return [];
        }

        return [$this->getDocument($content, $this->filePath)];
    }

    /**
     * @return Document[]
     * @throws Exception
     */
    private function getContentFromDirectory(string $path): array
    {
        $documents = [];
        // Open the directory
        if ($handle = opendir($path)) {
            // Read the directory contents
            while (($entry = readdir($handle)) !== false) {
                $fullPath = $path . '/' . $entry;
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($fullPath)) {
                        $documents = [...$documents, ...$this->getDocumentsFrom($fullPath)];
                    } else {
                        $content = $this->getContentFromFile($fullPath);
                        if ($content !== false) {
                            $documents[] = $this->getDocument($content, $entry);
                        }
                    }
                }
            }

            // Close the directory
            closedir($handle);
        }

        return $documents;
    }

    /**
     * @throws Exception
     */
    private function getContentFromFile(string $path): string|false
    {
        $fileExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (!$this->validExtension($fileExtension)) {
            return false;
        }

        if ($fileExtension === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);

            return $pdf->getText();
        }

        if ($fileExtension === 'docx') {
            $phpWord = IOFactory::load($path);
            $fullText = '';
            foreach ($phpWord->getSections() as $section) {
                $fullText .= $this->extractTextFromDocxNode($section);
            }

            return $fullText;
        }

        return file_get_contents($path);
    }

    private function extractTextFromDocxNode(Section|TextRun|Text $section): string
    {
        $text = '';
        if (method_exists($section, 'getText')) {
            $text .= $section->getText();
        } elseif (method_exists($section, 'getElements')) {
            /** @var Section|TextRun|Text $childSection */
            foreach ($section->getElements() as $childSection) {
                $text .= $this->extractTextFromDocxNode($childSection);
            }
        }

        return $text;
    }

    private function getDocument(string $content, string $entry): mixed
    {
        $document = new $this->documentClassName();
        $document->content = $content;
        $document->sourceType = $this->sourceType;
        $document->sourceName = $entry;

        return $document;
    }

    private function validExtension(string $fileExtension): bool
    {
        if (sizeof($this->extensions) === 0) {
            return true;
        }

        return in_array($fileExtension, $this->extensions);
    }
}

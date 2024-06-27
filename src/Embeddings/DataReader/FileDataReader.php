<?php

namespace LLPhant\Embeddings\DataReader;

use LLPhant\Embeddings\Document;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

final class FileDataReader implements DataReader
{
    public string $sourceType = 'files';

    private array $extensions;

    /**
     * @template T of Document
     *
     * @param  class-string<T>  $documentClassName
     */
    public function __construct(public string $filePath, public readonly string $documentClassName = Document::class)
    {
        $this->extensions = array(
            'docx',
            'pdf',
            'txt',
        );
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
            return $this->getDocumentsFromDirectory($this->filePath);
        }
        // If it's a file
        $content = $this->getContentFromFile($this->filePath);
        if ($content === false) {
            return [];
        }

        return [$this->getDocument($content, $this->filePath)];
    }

    /**
     * @return Document[]
     */
    private function getDocumentsFromDirectory(string $directory): array
    {
        $documents = [];
        // Open the directory
        if ($handle = opendir($directory)) {
            // Read the directory contents
            while (($entry = readdir($handle)) !== false) {
                $fullPath = $directory.'/'.$entry;
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($fullPath)) {
                        $documents = [...$documents, ...$this->getDocumentsFromDirectory($fullPath)];
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

    private function getContentFromFile(string $path): string|false
    {
        $fileExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (! $this->validExtension($fileExtension)) {
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
                foreach ($section->getElements() as $element) {
                    $fullText .= $this->extractTextFromDocxElement($element);
                }
            }

            return $fullText;
        }

        return file_get_contents($path);
    }

    private function extractTextFromDocxElement(AbstractElement $element): string
    {
        $text = '';
        if ($element instanceof AbstractContainer) {
            foreach ($element->getElements() as $element) {
                $text .= $this->extractTextFromDocxElement($element);
            }
        } elseif ($element instanceof Text) {
            $text .= $element->getText();
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
        if ($this->extensions === []) {
            return true;
        }

        return in_array($fileExtension, $this->extensions);
    }
}
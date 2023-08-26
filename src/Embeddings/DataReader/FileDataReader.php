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
                    $fullPath = $this->filePath.'/'.$entry;
                    if ($entry != '.' && $entry != '..' && is_file($fullPath)) {
                        $content = $this->getContentFromFile($fullPath);
                        if ($content !== false) {
                            $document = new $this->documentClassName();
                            $document->content = $content;
                            $document->sourceType = $this->sourceType;
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
        $content = $this->getContentFromFile($this->filePath);
        if ($content === false) {
            return [];
        }
        $document = new $this->documentClassName();
        $document->content = $content;
        $document->sourceType = $this->sourceType;
        $document->sourceName = $this->filePath;

        return [$document];
    }

    /**
     * @throws Exception
     */
    public function getContentFromFile(string $path): string|false
    {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);

            return $pdf->getText();
        }

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'docx') {
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
}

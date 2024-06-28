<?php

namespace LLPhant\Embeddings\DataReader;

use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\IOFactory;

class DocxReader
{
    public function getText(string $path): string
    {
        $phpWord = IOFactory::load($path);
        $fullText = '';
        foreach ($phpWord->getSections() as $section) {
            $fullText .= $this->extractTextFromDocxNode($section);
        }

        return $fullText;
    }

    private function extractTextFromDocxNode(AbstractElement $section): string
    {
        $text = '';
        if (method_exists($section, 'getElements')) {
            foreach ($section->getElements() as $childSection) {
                $text = $this->concatenate($text, $this->extractTextFromDocxNode($childSection));
            }
        } elseif (method_exists($section, 'getText')) {
            $text = $this->concatenate($text, $this->toString($section->getText()));
        }

        return $text;
    }

    private function concatenate(string $text1, string $text2): string
    {
        if ($text1 === '') {
            return $text1.$text2;
        }

        if (str_ends_with($text1, ' ')) {
            return $text1.$text2;
        }

        if (str_starts_with($text2, ' ')) {
            return $text1.$text2;
        }

        return $text1.' '.$text2;
    }

    /**
     * @param  array<string>|string|null  $text
     */
    private function toString(array|null|string $text): string
    {
        if ($text === null) {
            return '';
        }

        if (is_array($text)) {
            return implode(' ', $text);
        }

        return $text;
    }
}

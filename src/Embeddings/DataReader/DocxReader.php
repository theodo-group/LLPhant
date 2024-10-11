<?php

namespace LLPhant\Embeddings\DataReader;

use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
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

        return \htmlspecialchars_decode($fullText);
    }

    private function extractTextFromDocxNode(AbstractElement $section): string
    {
        $text = '';
        if (method_exists($section, 'getElements')) {
            foreach ($section->getElements() as $childSection) {
                $text = $this->concatenate($text, $this->extractTextFromDocxNode($childSection), $this->separatorFor($childSection));
            }
        } elseif (method_exists($section, 'getText')) {
            $text = $this->concatenate($text, $this->toString($section->getText()), $this->separatorFor($section));
        } elseif ($section instanceof TextBreak) {
            $text .= PHP_EOL;
        }

        return $text;
    }

    private function concatenate(string $text1, string $text2, string $separator): string
    {
        if ($text1 === '') {
            return $text2;
        }

        return $text1.$separator.$text2;
    }

    /**
     * @param  array<string>|string|null|TextRun  $text
     */
    private function toString(array|null|string|TextRun $text): string
    {
        if ($text === null) {
            return '';
        }

        if (is_array($text)) {
            return implode(' ', $text);
        }

        if ($text instanceof TextRun) {
            return $text->getText();
        }

        return $text;
    }

    private function separatorFor(AbstractElement $section): string
    {
        if ($section instanceof Title || $section instanceof TextRun) {
            return PHP_EOL;
        }

        return '';
    }
}

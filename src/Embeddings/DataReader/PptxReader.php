<?php

namespace LLPhant\Embeddings\DataReader;

use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\IOFactory;

class PptxReader
{
    public function getText(string $path): string
    {
        $reader = IOFactory::createReader('PowerPoint2007');
        $ppt = $reader->load($path);
        $fullText = '';

        foreach ($ppt->getAllSlides() as $slideIndex => $slide) {
            $fullText .= "Slide " . ($slideIndex + 1) . ": ";
            foreach ($slide->getShapeCollection() as $shape) {
                if ($shape instanceof RichText) {
                    foreach ($shape->getParagraphs() as $paragraph) {
                        $text = '';
                        foreach ($paragraph->getRichTextElements() as $element) {
                            $text .= $element->getText();
                        }
                        $fullText .= $text . " ";
                    }
                }
            }
        }

        return $fullText;
    }
}

<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory as WordIO;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Cell;
use Smalot\PdfParser\Parser as PdfParser;
use Throwable;

class ResumeTextExtractor
{
    public function __construct(private readonly PdfParser $pdfParser = new PdfParser())
    {
    }

    public function extract(string $absolutePath, string $extension): string
    {
        $extension = strtolower($extension);

        $raw = match ($extension) {
            'pdf' => $this->extractPdf($absolutePath),
            'docx' => $this->extractDocx($absolutePath),
            'doc' => $this->extractDocx($absolutePath),
            'txt' => (string) @file_get_contents($absolutePath),
            default => throw new \InvalidArgumentException("Unsupported file type: {$extension}"),
        };

        return $this->normalize($raw);
    }

    private function extractPdf(string $path): string
    {
        try {
            $document = $this->pdfParser->parseFile($path);
            return $document->getText();
        } catch (Throwable $e) {
            return '';
        }
    }

    private function extractDocx(string $path): string
    {
        try {
            $phpWord = WordIO::load($path);
        } catch (Throwable $e) {
            return '';
        }

        $buffer = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $buffer .= $this->elementText($element)."\n";
            }
        }

        return $buffer;
    }

    private function elementText(mixed $element): string
    {
        if ($element instanceof Text) {
            return $element->getText();
        }

        if ($element instanceof TextRun) {
            $line = '';
            foreach ($element->getElements() as $child) {
                $line .= $this->elementText($child).' ';
            }
            return trim($line);
        }

        if ($element instanceof ListItem) {
            return '- '.$element->getTextObject()->getText();
        }

        if ($element instanceof Table) {
            $rows = [];
            foreach ($element->getRows() as $row) {
                $cells = [];
                foreach ($row->getCells() as $cell) {
                    if ($cell instanceof Cell) {
                        $cellText = '';
                        foreach ($cell->getElements() as $child) {
                            $cellText .= $this->elementText($child).' ';
                        }
                        $cells[] = trim($cellText);
                    }
                }
                $rows[] = implode(' | ', $cells);
            }
            return implode("\n", $rows);
        }

        return '';
    }

    private function normalize(string $text): string
    {
        // Strip control chars, collapse runs of whitespace, unify line endings.
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? $text;
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;
        return trim($text);
    }
}

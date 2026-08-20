<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
use PhpOffice\PhpWord\IOFactory;

class WordDocumentParser
{
    private DocumentStyleMapper $styleMapper;

    public function __construct()
    {
        $this->styleMapper = new DocumentStyleMapper();
    }

    /**
     * Parse Word document and extract formatted HTML content
     */
    public function parseDocumentFile(string $filePath): string
    {
        try {
            if (!file_exists($filePath)) {
                \Log::error('Document file not found', ['path' => $filePath]);
                return '<p>Error: File not found.</p>';
            }

            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // Check file format
            if ($extension === 'doc') {
                return '<p><strong>Error: Unsupported format</strong></p>' .
                       '<p>Please convert .doc files to .docx format before uploading.</p>';
            }

            // Load and parse the document
            $phpWord = IOFactory::load($filePath);
            $html = '<div class="document-content">';
            $elementCount = 0;

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    try {
                        $parsed = $this->parseElement($element);
                        if (!empty($parsed)) {
                            $html .= $parsed;
                            $elementCount++;
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Error parsing element', [
                            'class' => get_class($element),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $html .= '</div>';

            // Sanitize and return
            return Purifier::clean($html, 'default');
        } catch (\Exception $e) {
            \Log::error('Error parsing document', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return '<p><strong>Error parsing document:</strong></p>' .
                   '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }

    /**
     * Parse individual element based on its type
     */
    private function parseElement($element): string
    {
        if ($element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
            return $this->styleMapper->buildHtmlElement($element, 'p');
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            return $this->parseTable($element);
        } else {
            // Handle any other element type (Title, TextRun, ListItem, etc)
            return $this->styleMapper->buildHtmlElement($element, 'p');
        }
    }

    /**
     * Parse table with proper formatting
     */
    private function parseTable(\PhpOffice\PhpWord\Element\Table $table): string
    {
        $html = '<table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd; margin: 10px 0;">';

        foreach ($table->getRows() as $row) {
            $html .= '<tr>';
            foreach ($row->getCells() as $cell) {
                $cellStyle = 'border: 1px solid #ddd; padding: 10px; vertical-align: top;';
                $html .= "<td style=\"$cellStyle\">";

                foreach ($cell->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
                        $cellContent = $this->styleMapper->buildHtmlElement($element, 'p');
                        if (!empty($cellContent)) {
                            $html .= $cellContent;
                        }
                    } elseif (method_exists($element, 'getText')) {
                        $text = $element->getText();
                        if (!empty(trim($text))) {
                            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                            $html .= "<p>$text</p>";
                        }
                    }
                }

                $html .= '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }
}

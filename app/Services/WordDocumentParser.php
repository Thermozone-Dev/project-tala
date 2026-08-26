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
            $inList = false;
            $listType = null;
            $lastListType = null;

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    try {
                        $isListItem = $this->isListItem($element);
                        $currentListType = $isListItem ? $this->getListType($element) : null;

                        // Close list if we transition from list to non-list
                        if ($inList && !$isListItem) {
                            $html .= "</$listType>";
                            $inList = false;
                            $listType = null;
                        }

                        // Close and reopen list if type changes
                        if ($inList && $isListItem && $currentListType !== $listType) {
                            $html .= "</$listType>";
                            $listType = $currentListType;
                            $html .= "<$listType style=\"margin: 10px 0; padding-left: 40px;\">";
                        }

                        // Open list if this is a list item and we're not already in one
                        if ($isListItem && !$inList) {
                            $listType = $currentListType;
                            $html .= "<$listType style=\"margin: 10px 0; padding-left: 40px;\">";
                            $inList = true;
                        }

                        $parsed = $this->parseElement($element);
                        if (!empty($parsed)) {
                            $html .= $parsed;
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Error parsing element', [
                            'class' => get_class($element),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Close any open list
            if ($inList) {
                $html .= "</$listType>";
            }

            $html .= '</div>';

            // Sanitize and return (allow more tags for better formatting)
            return $this->sanitizeHtml($html);
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
     * Check if element is a list item
     */
    private function isListItem($element): bool
    {
        try {
            $className = get_class($element);

            // Direct ListItem class check
            if (stripos($className, 'ListItem') !== false) {
                return true;
            }

            if ($element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
                if (method_exists($element, 'getStyle') && $element->getStyle()) {
                    $style = $element->getStyle();

                    // Check for numbering style
                    if (method_exists($style, 'getNumStyle') && $style->getNumStyle()) {
                        return true;
                    }

                    // Check for list style names
                    if (method_exists($style, 'getStyleName')) {
                        $styleName = $style->getStyleName();
                        if ($styleName && (
                            stripos($styleName, 'List') !== false ||
                            stripos($styleName, 'Bullet') !== false ||
                            stripos($styleName, 'Number') !== false
                        )) {
                            return true;
                        }
                    }
                }

                // Check indentation as heuristic for list items
                if (method_exists($element, 'getParagraphStyle')) {
                    $pStyle = $element->getParagraphStyle();
                    if ($pStyle && method_exists($pStyle, 'getIndentation')) {
                        $indent = $pStyle->getIndentation();
                        if ($indent && method_exists($indent, 'getLeft')) {
                            $left = $indent->getLeft();
                            // If heavily indented, likely a list item
                            if ($left && $left > 200) {
                                return true;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Not a list item
        }

        return false;
    }

    /**
     * Determine list type (ul or ol)
     */
    private function getListType($element): string
    {
        try {
            if (method_exists($element, 'getStyle') && $element->getStyle()) {
                $style = $element->getStyle();
                if (method_exists($style, 'getNumStyle')) {
                    $numStyle = $style->getNumStyle();
                    // Check if it's a numbered list
                    if ($numStyle && (stripos($numStyle, 'number') !== false || stripos($numStyle, 'decimal') !== false)) {
                        return 'ol';
                    }
                }
            }
        } catch (\Exception $e) {
            // Default to unordered
        }

        return 'ul';
    }

    /**
     * Sanitize HTML with permissive config for formatting preservation
     */
    private function sanitizeHtml(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();

        // Allow comprehensive formatting elements and attributes
        $config->set('HTML.Allowed',
            'div[style],p[style],br,strong,em,u,span[style],h1,h2,h3,h4,h5,h6,' .
            'ul,ol,li,table[style],tr,td[style],th[style],tbody,thead,' .
            'img[src|alt|width|height|style],a[href|style|target|rel|class]'
        );

        // Allow comprehensive CSS for formatting
        $config->set('CSS.AllowedProperties',
            'text-align,text-indent,color,font-size,font-weight,font-style,text-decoration,' .
            'margin,margin-left,margin-right,margin-top,margin-bottom,' .
            'padding,padding-left,padding-right,padding-top,padding-bottom,' .
            'line-height,border,border-collapse,width,height,' .
            'background-color,vertical-align'
        );

        // Allow safe URL schemes
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'data' => true]);

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }

    /**
     * Parse individual element based on its type
     */
    private function parseElement($element): string
    {
        if ($element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
            return $this->parseParagraph($element);
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            return $this->parseTable($element);
        } elseif ($element instanceof \PhpOffice\PhpWord\Element\ListItem) {
            return $this->parseListItem($element);
        } else {
            // Handle any other element type (Title, TextRun, etc)
            return $this->styleMapper->buildHtmlElement($element, 'p');
        }
    }

    /**
     * Parse paragraph including images and special list handling
     */
    private function parseParagraph($element): string
    {
        // Check if this is a list item
        try {
            if (method_exists($element, 'getStyle') && $element->getStyle()) {
                $style = $element->getStyle();
                if (method_exists($style, 'getNumStyle') && $style->getNumStyle()) {
                    return $this->parseListItem($element);
                }
            }
        } catch (\Exception $e) {
            // Not a list item, continue as normal paragraph
        }

        // Parse images in the paragraph
        $html = '';
        try {
            if (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $child) {
                    if ($child instanceof \PhpOffice\PhpWord\Element\Image) {
                        $html .= $this->parseImage($child);
                    }
                }
            }
        } catch (\Exception $e) {
            // No images found
        }

        // Add the paragraph content
        $paragraphHtml = $this->styleMapper->buildHtmlElement($element, 'p');
        $html .= $paragraphHtml;

        return $html;
    }

    /**
     * Parse list items with proper bullet/number handling
     */
    private function parseListItem($element): string
    {
        try {
            $tag = $this->styleMapper->determineTag($element, 'li');
            $formatted = $this->styleMapper->extractFormattedText($element);

            if (!$formatted['hasContent']) {
                return '';
            }

            $paragraphStyle = $this->styleMapper->getParagraphStyle($element);

            // Ensure proper spacing for list items
            $style = $paragraphStyle;
            if (empty($style)) {
                $style = ' style="margin: 5px 0;"';
            } else {
                // Append margin if not already there
                if (stripos($style, 'margin') === false) {
                    $style = str_replace('style="', 'style="margin: 5px 0; ', $style);
                }
            }

            return "<$tag$style>{$formatted['text']}</$tag>";
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Extract and convert images from DOCX
     */
    private function parseImage($image): string
    {
        try {
            if (!$image instanceof \PhpOffice\PhpWord\Element\Image) {
                return '';
            }

            // Get image source and properties
            $source = $image->getSource();
            $width = $image->getWidth();
            $height = $image->getHeight();

            // Create data URI or extract image
            $imageData = $this->extractImageData($source);

            if ($imageData) {
                $style = 'max-width: 100%; height: auto;';
                if ($width) {
                    $style .= " width: {$width}px;";
                }
                if ($height) {
                    $style .= " height: {$height}px;";
                }
                return "<div style=\"margin: 15px 0; text-align: center;\"><img src=\"$imageData\" style=\"$style\" alt=\"Document image\"></div>";
            }
        } catch (\Exception $e) {
            \Log::warning('Error parsing image', ['error' => $e->getMessage()]);
        }

        return '';
    }

    /**
     * Extract image data as base64 or file path
     */
    private function extractImageData($source): ?string
    {
        try {
            // If source is a URL or file path, return it as-is
            if (is_string($source) && (strpos($source, 'http') === 0 || file_exists($source))) {
                return $source;
            }

            // If source has getImageBinaryData method, convert to base64
            if (is_object($source) && method_exists($source, 'getImageBinaryData')) {
                $binaryData = $source->getImageBinaryData();
                if ($binaryData) {
                    $mimeType = $this->getImageMimeType($source);
                    $base64 = base64_encode($binaryData);
                    return "data:$mimeType;base64,$base64";
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Error extracting image data', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Determine MIME type for image
     */
    private function getImageMimeType($image): string
    {
        try {
            if (method_exists($image, 'getMimeType')) {
                return $image->getMimeType();
            }

            // Fallback to common image types
            if (method_exists($image, 'getSource')) {
                $source = (string)$image->getSource();
                if (stripos($source, 'png') !== false) {
                    return 'image/png';
                }
                if (stripos($source, 'jpg') !== false || stripos($source, 'jpeg') !== false) {
                    return 'image/jpeg';
                }
                if (stripos($source, 'gif') !== false) {
                    return 'image/gif';
                }
            }
        } catch (\Exception $e) {
            // Return default
        }

        return 'image/jpeg';
    }

    /**
     * Parse table with proper formatting
     */
    private function parseTable($table): string
    {
        $html = '<table style="border-collapse: collapse; width: 100%; border: 1px solid #999; margin: 15px 0; font-size: 14px;">';

        $isFirstRow = true;
        foreach ($table->getRows() as $row) {
            $html .= '<tr>';
            foreach ($row->getCells() as $cell) {
                // Use th for first row (header)
                $tag = $isFirstRow ? 'th' : 'td';
                $headerStyle = $isFirstRow ? 'background-color: #f2f2f2; font-weight: bold;' : '';
                $cellStyle = 'border: 1px solid #999; padding: 12px; vertical-align: top; ' . $headerStyle;

                // Get cell alignment if available
                try {
                    if (method_exists($cell, 'getWidth')) {
                        $width = $cell->getWidth();
                        if ($width) {
                            $cellStyle .= "width: {$width}px;";
                        }
                    }
                } catch (\Exception $e) {
                    // Width not available
                }

                $html .= "<$tag style=\"$cellStyle\">";

                foreach ($cell->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
                        $cellContent = $this->styleMapper->buildHtmlElement($element, 'p');
                        if (!empty($cellContent)) {
                            $html .= $cellContent;
                        }
                    } elseif (method_exists($element, 'getText')) {
                        $text = $element->getText();
                        // Ensure text is a string before trimming
                        if (is_string($text) && !empty(trim($text))) {
                            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
                            $html .= "<p style=\"margin: 0;\">$text</p>";
                        }
                    }
                }

                $html .= "</$tag>";
            }
            $html .= '</tr>';
            $isFirstRow = false;
        }

        $html .= '</table>';
        return $html;
    }
}

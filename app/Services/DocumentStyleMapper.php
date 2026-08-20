<?php

namespace App\Services;

use PhpOffice\PhpWord\Element\Text;

class DocumentStyleMapper
{
    /**
     * Map font styles to HTML tags/attributes
     */
    public function applyTextFormatting(string $text, $fontStyle): string
    {
        if (!$fontStyle) {
            return $text;
        }

        try {
            if (method_exists($fontStyle, 'isBold') && $fontStyle->isBold()) {
                $text = "<strong>$text</strong>";
            }

            if (method_exists($fontStyle, 'isItalic') && $fontStyle->isItalic()) {
                $text = "<em>$text</em>";
            }

            // Check for underline - must be explicit true value
            if (method_exists($fontStyle, 'getUnderline')) {
                $underline = $fontStyle->getUnderline();
                // Only apply underline if it's explicitly set to 'single' or true
                if ($underline && $underline !== 'none' && $underline !== false && $underline !== null && $underline !== 0) {
                    $text = "<u>$text</u>";
                }
            }

            // Add color if available
            if (method_exists($fontStyle, 'getColor')) {
                $color = $fontStyle->getColor();
                if ($color && method_exists($color, 'getRgb')) {
                    $colorValue = $color->getRgb();
                    if ($colorValue && $colorValue !== '000000' && $colorValue !== 'auto') {
                        $text = "<span style=\"color: #$colorValue;\">$text</span>";
                    }
                }
            }

            // Add font size if available
            if (method_exists($fontStyle, 'getSize')) {
                $size = $fontStyle->getSize();
                if ($size && $size > 0) {
                    $text = "<span style=\"font-size: {$size}pt;\">$text</span>";
                }
            }
        } catch (\Exception $e) {
            // If style extraction fails, return text as-is
        }

        return $text;
    }

    /**
     * Determine appropriate HTML tag based on element style
     */
    public function determineTag($element, string $defaultTag = 'p'): string
    {
        $tag = $defaultTag;

        try {
            // Check by element class name
            $className = get_class($element);
            if (stripos($className, 'Title') !== false) {
                return 'h1';
            }
            if (stripos($className, 'ListItem') !== false) {
                return 'li';
            }

            // Check by style name
            if (method_exists($element, 'getStyle')) {
                $style = $element->getStyle();
                if ($style && method_exists($style, 'getStyleName')) {
                    $styleName = $style->getStyleName();

                    if (stripos($styleName, 'Heading 1') !== false) {
                        return 'h1';
                    }
                    if (stripos($styleName, 'Heading 2') !== false) {
                        return 'h2';
                    }
                    if (stripos($styleName, 'Heading 3') !== false) {
                        return 'h3';
                    }
                }
            }
        } catch (\Exception $e) {
            // Tag determination failed, use default
        }

        return $tag;
    }

    /**
     * Extract paragraph-level styles (alignment, indentation, etc)
     */
    public function getParagraphStyle($element): string
    {
        $styles = [];

        try {
            if (method_exists($element, 'getParagraphStyle')) {
                $pStyle = $element->getParagraphStyle();
                if ($pStyle) {
                    // Text alignment
                    if (method_exists($pStyle, 'getAlignment')) {
                        $alignment = $pStyle->getAlignment();
                        if ($alignment === 'center') {
                            $styles[] = 'text-align: center';
                        } elseif ($alignment === 'right') {
                            $styles[] = 'text-align: right';
                        } elseif ($alignment === 'justify') {
                            $styles[] = 'text-align: justify';
                        }
                    }

                    // Indentation
                    if (method_exists($pStyle, 'getIndentation')) {
                        $indent = $pStyle->getIndentation();
                        if ($indent && method_exists($indent, 'getLeft')) {
                            $left = $indent->getLeft();
                            if ($left > 0) {
                                $styles[] = "margin-left: " . ($left / 20) . "mm";
                            }
                        }
                    }

                    // Line spacing
                    if (method_exists($pStyle, 'getLineSpacing')) {
                        $lineSpacing = $pStyle->getLineSpacing();
                        if ($lineSpacing) {
                            // Convert to CSS line-height
                            $styles[] = "line-height: {$lineSpacing}";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Paragraph style extraction failed
        }

        return !empty($styles) ? ' style="' . implode('; ', $styles) . ';"' : '';
    }

    /**
     * Extract formatted text from element's child elements or rich text elements
     */
    public function extractFormattedText($element): array
    {
        $result = [
            'text' => '',
            'hasContent' => false,
        ];

        try {
            // Try getElements() for TextRun, ListItemRun, etc.
            if (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $childElement) {
                    if ($childElement instanceof Text) {
                        $text = htmlspecialchars($childElement->getText(), ENT_QUOTES, 'UTF-8');

                        if (!empty(trim($text))) {
                            $result['hasContent'] = true;

                            // Apply text formatting
                            $fontStyle = $childElement->getFontStyle();
                            $text = $this->applyTextFormatting($text, $fontStyle);

                            $result['text'] .= $text;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Element iteration failed
        }

        // Try getRichTextElements() for Paragraph elements
        try {
            if (!$result['hasContent'] && method_exists($element, 'getRichTextElements')) {
                foreach ($element->getRichTextElements() as $textElement) {
                    if ($textElement instanceof Text) {
                        $text = htmlspecialchars($textElement->getText(), ENT_QUOTES, 'UTF-8');

                        if (!empty(trim($text))) {
                            $result['hasContent'] = true;

                            // Apply text formatting
                            $fontStyle = $textElement->getFontStyle();
                            $text = $this->applyTextFormatting($text, $fontStyle);

                            $result['text'] .= $text;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Rich text extraction failed
        }

        // Fallback to plain getText()
        if (!$result['hasContent'] && method_exists($element, 'getText')) {
            $text = $element->getText();
            if (!empty(trim($text))) {
                $result['hasContent'] = true;
                $result['text'] = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            }
        }

        return $result;
    }

    /**
     * Build complete HTML element with all formatting
     */
    public function buildHtmlElement($element, string $defaultTag = 'p'): string
    {
        try {
            $tag = $this->determineTag($element, $defaultTag);
            $formatted = $this->extractFormattedText($element);

            if (!$formatted['hasContent']) {
                return '';
            }

            $paragraphStyle = $this->getParagraphStyle($element);

            return "<$tag$paragraphStyle>{$formatted['text']}</$tag>";
        } catch (\Exception $e) {
            return '';
        }
    }
}

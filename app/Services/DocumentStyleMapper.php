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
                // PhpWord returns size in half-points (e.g., 24 for 12pt)
                if ($size && $size > 0) {
                    $sizeInPt = $size;
                    // Only apply if it's noticeably different from default (12pt)
                    if ($sizeInPt != 12) {
                        $text = "<span style=\"font-size: {$sizeInPt}pt;\">$text</span>";
                    }
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

            // Check by style or paragraph properties
            if (method_exists($element, 'getStyle')) {
                $style = $element->getStyle();
                if ($style && method_exists($style, 'getStyleName')) {
                    $styleName = $style->getStyleName();

                    if (stripos($styleName, 'Heading 1') !== false || stripos($styleName, 'h1') !== false) {
                        return 'h1';
                    }
                    if (stripos($styleName, 'Heading 2') !== false || stripos($styleName, 'h2') !== false) {
                        return 'h2';
                    }
                    if (stripos($styleName, 'Heading 3') !== false || stripos($styleName, 'h3') !== false) {
                        return 'h3';
                    }
                    if (stripos($styleName, 'Heading 4') !== false || stripos($styleName, 'h4') !== false) {
                        return 'h4';
                    }
                    if (stripos($styleName, 'Heading 5') !== false || stripos($styleName, 'h5') !== false) {
                        return 'h5';
                    }
                    if (stripos($styleName, 'Heading 6') !== false || stripos($styleName, 'h6') !== false) {
                        return 'h6';
                    }
                }
            }

            // Check for list item numbering style
            if (method_exists($element, 'getStyle') && method_exists($element->getStyle(), 'getNumStyle')) {
                if ($element->getStyle()->getNumStyle()) {
                    return 'li';
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
                    // Text alignment - check multiple possible methods
                    $alignment = null;

                    // Try different methods to get alignment
                    if (method_exists($pStyle, 'getAlignment')) {
                        $alignment = $pStyle->getAlignment();
                    }

                    if (!$alignment && method_exists($pStyle, 'getJustification')) {
                        $alignment = $pStyle->getJustification();
                    }

                    // Map alignment values
                    if ($alignment) {
                        $alignmentStr = (string)$alignment;
                        $alignmentLower = strtolower($alignmentStr);

                        // Use strict string comparison
                        if ($alignmentLower === 'center') {
                            $styles[] = 'text-align: center';
                        } elseif ($alignmentLower === 'right') {
                            $styles[] = 'text-align: right';
                        } elseif ($alignmentLower === 'distribute' || $alignmentLower === 'justify' || $alignmentLower === 'both') {
                            $styles[] = 'text-align: justify';
                        } elseif ($alignmentLower === 'left') {
                            // Only add explicit left if there are other styles
                            if (!empty($styles)) {
                                $styles[] = 'text-align: left';
                            }
                        }
                    }

                    // Indentation (convert twips to pixels: 1 twip = 0.05pt = ~0.66px)
                    if (method_exists($pStyle, 'getIndentation')) {
                        $indent = $pStyle->getIndentation();
                        if ($indent) {
                            if (method_exists($indent, 'getLeft')) {
                                $left = $indent->getLeft();
                                if ($left && $left > 0) {
                                    // Convert twips to pixels (1 twip = 1/1440 inch, ~0.0667px)
                                    $leftPx = round($left * 0.05);
                                    if ($leftPx > 0) {
                                        $styles[] = "margin-left: {$leftPx}px";
                                    }
                                }
                            }
                            if (method_exists($indent, 'getRight')) {
                                $right = $indent->getRight();
                                if ($right && $right > 0) {
                                    // Convert twips to pixels
                                    $rightPx = round($right * 0.05);
                                    if ($rightPx > 0) {
                                        $styles[] = "margin-right: {$rightPx}px";
                                    }
                                }
                            }
                            // First line indentation
                            if (method_exists($indent, 'getFirstLine')) {
                                $firstLine = $indent->getFirstLine();
                                if ($firstLine && $firstLine > 0) {
                                    $firstLinePx = round($firstLine * 0.05);
                                    if ($firstLinePx > 0) {
                                        $styles[] = "text-indent: {$firstLinePx}px";
                                    }
                                }
                            }
                        }
                    }

                    // Line spacing
                    if (method_exists($pStyle, 'getLineSpacing')) {
                        $lineSpacing = $pStyle->getLineSpacing();
                        if ($lineSpacing && $lineSpacing !== 1) {
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
            // Ensure text is a string before processing
            if (is_string($text) && !empty(trim($text))) {
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

<?php

declare(strict_types=1);

namespace App\Helpers;

final class HtmlSanitizer
{
    private const array ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a',
        'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'img', 'figure', 'figcaption',
        'hr', 'sup', 'sub', 'span',
    ];

    private const array ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    public static function sanitize(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        // Strip to allowed tags
        $allowedTagStr = implode('', array_map(
            fn (string $tag): string => "<{$tag}>",
            self::ALLOWED_TAGS,
        ));
        $html = strip_tags($html, $allowedTagStr);

        // Remove dangerous attributes (onclick, onerror, style with expressions, etc.)
        $html = (string) preg_replace('/\s*on\w+\s*=\s*"[^"]*"/i', '', $html);
        $html = (string) preg_replace('/\s*on\w+\s*=\s*\'[^\']*\'/i', '', $html);

        // Remove javascript: protocol from hrefs
        $html = (string) preg_replace('/href\s*=\s*"javascript:[^"]*"/i', 'href="#"', $html);
        $html = (string) preg_replace("/href\s*=\s*'javascript:[^']*'/i", "href='#'", $html);

        // Remove data: protocol from src attributes (except data:image for base64 images)
        $html = (string) preg_replace('/src\s*=\s*"data:(?!image)[^"]*"/i', 'src=""', $html);

        // Remove empty paragraphs
        $html = (string) preg_replace('/<p>\s*(&nbsp;)?\s*<\/p>/i', '', $html);

        return trim($html);
    }
}
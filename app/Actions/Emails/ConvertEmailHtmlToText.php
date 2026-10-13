<?php

declare(strict_types=1);

namespace App\Actions\Emails;

/**
 * Builds the plain-text version of an email from its sanitized HTML: paragraphs
 * become blank-line separated, list items get bullets or numbers, and links keep
 * their address in brackets.
 */
final class ConvertEmailHtmlToText
{
    public function __invoke(string $html): string
    {
        $text = preg_replace_callback(
            '#<a\b[^>]*href="([^"]*)"[^>]*>(.*?)</a>#is',
            function (array $match): string {
                $url = html_entity_decode($match[1]);
                $label = mb_trim(strip_tags($match[2]));

                return $label === '' || html_entity_decode($label) === $url || 'mailto:'.html_entity_decode($label) === $url
                    ? $url
                    : sprintf('%s (%s)', $match[2], $url);
            },
            $html,
        ) ?? $html;

        $text = preg_replace_callback(
            '#<ol\b[^>]*>(.*?)</ol>#is',
            function (array $match): string {
                $number = 0;

                $items = preg_replace_callback('#<li\b[^>]*>#i', function () use (&$number): string {
                    $number++;

                    return "\n{$number}. ";
                }, $match[1]) ?? $match[1];

                return $items."\n\n";
            },
            $text,
        ) ?? $text;

        $text = preg_replace(
            ['#<br\s*/?>#i', '#<li\b[^>]*>#i', '#</(p|ul|ol)>#i'],
            ["\n", "\n- ", "\n\n"],
            $text,
        ) ?? $text;

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5);
        $text = preg_replace("/[ \t]+\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return mb_trim($text);
    }
}

<?php

declare(strict_types=1);

namespace App\Mail\Concerns;

use Illuminate\Support\HtmlString;

trait FormatsPlainTextBody
{
    /**
     * Escapes a plain-text body and turns it into paragraphs, keeping single
     * line breaks and making bare links clickable.
     *
     * @return list<HtmlString>
     */
    protected function paragraphsFrom(string $text): array
    {
        $blocks = preg_split('/\R{2,}/u', mb_trim(str_replace("\r\n", "\n", $text))) ?: [];

        return array_values(array_map(function (string $block): HtmlString {
            $escaped = e(mb_trim($block));
            $linked = preg_replace_callback(
                '#\bhttps?://[^\s<]+#i',
                function (array $match): string {
                    $url = mb_rtrim($match[0], '.,;:!?)');
                    $trailing = mb_substr($match[0], mb_strlen($url));

                    return sprintf('<a href="%s" style="color:#1a73e8;">%s</a>%s', $url, $url, $trailing);
                },
                $escaped,
            ) ?? $escaped;

            return new HtmlString(nl2br($linked, false));
        }, array_filter($blocks, fn (string $block): bool => mb_trim($block) !== '')));
    }
}

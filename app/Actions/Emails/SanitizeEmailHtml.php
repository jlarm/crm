<?php

declare(strict_types=1);

namespace App\Actions\Emails;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Reduces HTML from the email editor to the formatting its toolbar offers.
 *
 * The editor only produces these tags, but the request can carry anything, so
 * everything else (scripts, styles, images, event handlers) is stripped here
 * before the email is saved, shown in the CRM or sent.
 */
final class SanitizeEmailHtml
{
    public function __invoke(string $html): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('u')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('a', ['href'])
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->forceAttribute('a', 'target', '_blank')
            ->withMaxInputLength(200_000);

        $clean = (new HtmlSanitizer($config))->sanitize($html);

        // The editor wraps each list item's text in a paragraph, which email clients space out.
        $clean = preg_replace('#<li>\s*<p>(.*?)</p>\s*</li>#s', '<li>$1</li>', $clean) ?? $clean;

        // An empty editor still submits an empty paragraph.
        return mb_trim(strip_tags($clean)) === '' ? '' : mb_trim($clean);
    }
}

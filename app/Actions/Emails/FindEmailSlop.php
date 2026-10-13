<?php

declare(strict_types=1);

namespace App\Actions\Emails;

/**
 * Flags the wording that makes an email read as machine-written: banned words,
 * empty filler, stock email openers and closers, contrast tricks, em dashes and
 * leftover placeholders. Used to send an AI draft back for one more pass.
 */
final class FindEmailSlop
{
    /**
     * @var list<string>
     */
    private const array BANNED_WORDS = [
        'delve', 'foster', 'leverage', 'utilize', 'facilitate', 'empower', 'streamline', 'robust',
        'cutting-edge', 'paradigm shift', 'game changer', 'game-changer', 'tapestry', 'realm', 'beacon',
        'multifaceted', 'meticulous', 'intricate', 'paramount', 'transformative', 'elevate', 'embark',
        'supercharge', 'harness', 'ever-evolving', 'seamless', 'synergy', 'holistic', 'unlock',
        'navigate the', 'landscape',
    ];

    /**
     * @var list<string>
     */
    private const array FILLER_PHRASES = [
        "it's worth noting", 'it is worth noting', "it's important to note", 'at the end of the day',
        'when it comes to', 'at its core', "in today's", 'in the age of', 'the reality is', 'the truth is',
        'going forward', "here's the thing", 'let me be clear', "i'll be honest", 'rest assured',
        'i hope this email finds you', 'i hope this message finds you', 'i hope you are doing well',
        "i hope you're doing well", 'i hope all is well', 'i wanted to reach out', 'i am reaching out',
        "i'm reaching out", 'just checking in', 'just following up', 'circle back', 'touch base',
        "don't hesitate to", 'do not hesitate to', 'feel free to reach out', 'i trust this',
        'thank you for your time and consideration', 'great question', 'good question', 'thanks for reaching out', 'please let me know if you have any questions',
    ];

    /**
     * @return list<string> Each problem, phrased as an instruction for the rewrite.
     */
    public function __invoke(string $text): array
    {
        $lower = mb_strtolower($text);
        $problems = [];

        foreach (self::BANNED_WORDS as $word) {
            if (preg_match('/\b'.preg_quote($word, '/').'/u', $lower) === 1) {
                $problems[] = sprintf('Remove the word "%s".', $word);
            }
        }

        foreach (self::FILLER_PHRASES as $phrase) {
            if (str_contains($lower, $phrase)) {
                $problems[] = sprintf('Cut the stock phrase "%s" and say the point directly.', $phrase);
            }
        }

        if (preg_match('/\\b(not just|not only)\\b[^.?!]{1,80}\\bbut\\b/u', $lower) === 1
            || preg_match("/\\b(?:isn't|is not|it's not|it is not)\\b[^.?!]{1,60}[.,;]\\s*it'?s\\b/u", $lower) === 1) {
            $problems[] = 'Drop the "not X, but Y" contrast and state the point directly.';
        }

        if (str_contains($text, '—') || str_contains($text, '–')) {
            $problems[] = 'Use no em or en dashes; use commas or periods.';
        }

        if (preg_match('/\[[^\]]{2,40}\]|\{\{[^}]+\}\}|<[A-Z][a-z ]+>/u', $text) === 1) {
            $problems[] = 'Remove placeholders like [Name]; leave out anything you do not know.';
        }

        if (preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $text) === 1) {
            $problems[] = 'Remove emoji.';
        }

        if (preg_match('/\*\*[^*]+\*\*|^\s*[-*•]\s/mu', $text) === 1) {
            $problems[] = 'Write plain paragraphs: no bold and no bullet lists.';
        }

        return $problems;
    }
}

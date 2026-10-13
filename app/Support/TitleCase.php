<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Turns ALL-CAPS text, common in dealer lists and imports, into title case:
 * "5 CORNERS DODGE CHRYSLER JEEP RAM" becomes "5 Corners Dodge Chrysler Jeep Ram".
 *
 * Only text written entirely in capitals is changed. Anything already in mixed
 * case ("McKinney Ford", "BMW of Elgin") was typed deliberately and is kept.
 */
final class TitleCase
{
    /**
     * Words that stay in capitals, mapped to how they're written.
     *
     * @var array<string, string>
     */
    private const array ACRONYMS = [
        'LLC' => 'LLC', 'LLP' => 'LLP', 'LP' => 'LP', 'PC' => 'PC', 'PLLC' => 'PLLC', 'USA' => 'USA', 'US' => 'US',
        'RV' => 'RV', 'RVS' => 'RVs', 'ATV' => 'ATV', 'UTV' => 'UTV', 'BMW' => 'BMW', 'GMC' => 'GMC', 'GM' => 'GM',
        'CDJR' => 'CDJR', 'CJDR' => 'CJDR', 'VW' => 'VW', 'KTM' => 'KTM', 'BRP' => 'BRP', 'AMG' => 'AMG',
        'II' => 'II', 'III' => 'III', 'IV' => 'IV', 'PO' => 'PO',
        'NE' => 'NE', 'NW' => 'NW', 'SE' => 'SE', 'SW' => 'SW',
        'CEO' => 'CEO', 'CFO' => 'CFO', 'COO' => 'COO', 'CIO' => 'CIO', 'CTO' => 'CTO', 'VP' => 'VP', 'SVP' => 'SVP',
        'EVP' => 'EVP', 'GSM' => 'GSM', 'IT' => 'IT', 'HR' => 'HR', 'BDC' => 'BDC', 'DP' => 'DP',
    ];

    /**
     * Short words kept lowercase unless they start the text.
     *
     * @var list<string>
     */
    private const array SMALL_WORDS = ['a', 'an', 'and', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to'];

    public static function fromAllCaps(?string $value): ?string
    {
        if ($value === null || ! self::isAllCaps($value)) {
            return $value;
        }

        $position = 0;

        return preg_replace_callback(
            // "route" matches (empty) when the word is followed by "-" and a digit.
            "/[\\p{L}\\p{N}][\\p{L}\\p{N}'’]*(?<route>(?=-\\p{N}))?/u",
            function (array $match) use (&$position): string {
                $word = $match[0];

                // Route numbers in addresses keep their state code: "SC-72", "NY-17".
                if ($match['route'] !== null && mb_strlen($word) === 2) {
                    $position++;

                    return $word;
                }

                return self::word($word, $position++ === 0);
            },
            $value,
            flags: PREG_UNMATCHED_AS_NULL,
        ) ?? $value;
    }

    public static function isAllCaps(string $value): bool
    {
        return preg_match('/\p{Lu}.*\p{Lu}/u', $value) === 1 && mb_strtoupper($value) === $value;
    }

    private static function word(string $word, bool $isFirst): string
    {
        if (isset(self::ACRONYMS[$word])) {
            return self::ACRONYMS[$word];
        }

        $lower = mb_strtolower($word);

        if (! $isFirst && in_array($lower, self::SMALL_WORDS, true)) {
            return $lower;
        }

        // "1ST", "4X4": numbers keep their letters lowercase.
        if (preg_match('/^\p{N}/u', $lower) === 1) {
            return $lower;
        }

        $titled = mb_strtoupper(mb_substr($lower, 0, 1)).mb_substr($lower, 1);

        // McDonald, O'Brien, D'Angelo.
        $titled = preg_replace_callback('/^(Mc)(\p{Ll})(?=\p{L})/u', fn (array $m): string => $m[1].mb_strtoupper($m[2]), $titled) ?? $titled;

        return preg_replace_callback("/^([ODL]['’])(\\p{Ll})/u", fn (array $m): string => $m[1].mb_strtoupper($m[2]), $titled) ?? $titled;
    }
}

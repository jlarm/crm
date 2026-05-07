<?php

declare(strict_types=1);

describe('format_phone helper', function (): void {
    it('formats a 10-digit string into US phone format', function (): void {
        expect(format_phone('5551234567'))->toBe('(555) 123-4567');
    });

    it('strips non-numeric characters before formatting', function (): void {
        expect(format_phone('(555) 123-4567'))->toBe('(555) 123-4567');
        expect(format_phone('555.123.4567'))->toBe('(555) 123-4567');
        expect(format_phone('555-123-4567'))->toBe('(555) 123-4567');
    });

    it('returns the digits unchanged when not exactly 10 digits', function (): void {
        expect(format_phone('12345'))->toBe('12345');
        expect(format_phone('123456789012'))->toBe('123456789012');
    });

    it('returns empty string when input has no digits', function (): void {
        expect(format_phone('abc'))->toBe('');
    });
});

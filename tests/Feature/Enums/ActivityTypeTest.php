<?php

declare(strict_types=1);

use App\Enum\ActivityType;

describe('ActivityType', function (): void {
    it('returns a label for every case', function (): void {
        expect(ActivityType::Call->label())->toBe('Call')
            ->and(ActivityType::Note->label())->toBe('Note')
            ->and(ActivityType::Email->label())->toBe('Email');
    });

    it('returns an icon for every case', function (): void {
        expect(ActivityType::Call->icon())->toBe('phone')
            ->and(ActivityType::Note->icon())->toBe('file-text')
            ->and(ActivityType::Email->icon())->toBe('mail');
    });
});

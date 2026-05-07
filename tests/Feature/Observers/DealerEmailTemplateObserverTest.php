<?php

declare(strict_types=1);

use App\Models\DealerEmailTemplate;
use Illuminate\Support\Facades\Storage;

describe('DealerEmailTemplateObserver', function (): void {
    beforeEach(function (): void {
        Storage::fake('public');
    });

    it('deletes the stored attachment when the template is deleted', function (): void {
        Storage::disk('public')->put('attachments/file.pdf', 'data');
        $template = DealerEmailTemplate::factory()->create([
            'attachment_path' => 'attachments/file.pdf',
            'attachment_name' => 'file.pdf',
        ]);

        $template->delete();

        expect(Storage::disk('public')->exists('attachments/file.pdf'))->toBeFalse();
    });

    it('does not attempt to delete on destroy when attachment_path is null', function (): void {
        Storage::disk('public')->put('attachments/other.pdf', 'data');
        $template = DealerEmailTemplate::factory()->create([
            'attachment_path' => null,
            'attachment_name' => null,
        ]);

        $template->delete();

        // Unrelated file remains untouched and no exception was thrown.
        expect(Storage::disk('public')->exists('attachments/other.pdf'))->toBeTrue();
    });

    it('does not delete anything on update when the original attachment value is not a string', function (): void {
        Storage::disk('public')->put('attachments/keep.pdf', 'data');
        $template = DealerEmailTemplate::factory()->create([
            'attachment_path' => 'attachments/keep.pdf',
            'attachment_name' => 'keep.pdf',
        ]);

        $template->update([
            'attachment_path' => 'attachments/new.pdf',
        ]);

        // Old file remains because original 'attachment' attribute is not a string.
        expect(Storage::disk('public')->exists('attachments/keep.pdf'))->toBeTrue();
    });
});

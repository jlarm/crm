<?php

declare(strict_types=1);

use App\Models\DealerEmail;
use App\Models\DealerEmailTemplate;
use App\Models\PdfAttachment;
use App\Models\User;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

describe('PdfAttachment model', function (): void {
    it('can be created with file_name and file_path', function (): void {
        $attachment = PdfAttachment::create([
            'file_name' => 'brochure.pdf',
            'file_path' => 'attachments/brochure.pdf',
        ]);

        expect($attachment->file_name)->toBe('brochure.pdf')
            ->and($attachment->file_path)->toBe('attachments/brochure.pdf')
            ->and($attachment->id)->toBeGreaterThan(0);
    });

    it('can be morphed to a DealerEmail', function (): void {
        $attachment = PdfAttachment::create([
            'file_name' => 'a.pdf',
            'file_path' => 'a.pdf',
        ]);

        $dealerEmail = DealerEmail::factory()->create();
        $dealerEmail->pdfAttachments()->attach($attachment);

        expect($attachment->attachable()->count())->toBe(1)
            ->and($attachment->attachable->first()?->is($dealerEmail))->toBeTrue();
    });

    it('can be morphed to a DealerEmailTemplate', function (): void {
        $attachment = PdfAttachment::create([
            'file_name' => 'b.pdf',
            'file_path' => 'b.pdf',
        ]);

        $template = DealerEmailTemplate::factory()->create();
        $template->pdfAttachments()->attach($attachment);

        expect($attachment->attachableTemplate()->count())->toBe(1)
            ->and($attachment->attachableTemplate->first()?->is($template))->toBeTrue();
    });

    it('logs activity on create', function (): void {
        $attachment = PdfAttachment::create([
            'file_name' => 'c.pdf',
            'file_path' => 'c.pdf',
        ]);

        expect($attachment->activities()->count())->toBeGreaterThan(0);
    });
});

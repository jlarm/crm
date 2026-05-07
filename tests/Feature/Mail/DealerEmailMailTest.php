<?php

declare(strict_types=1);

use App\Mail\DealerEmailMail;
use App\Models\DealerEmail;
use App\Models\DealerEmailTemplate;
use App\Models\Dealership;
use App\Models\PdfAttachment;
use App\Models\User;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

describe('DealerEmailMail', function (): void {
    it('uses the template subject and body when not customized and replaces placeholders', function (): void {
        $template = DealerEmailTemplate::factory()->create([
            'subject' => 'Template Subject',
            'body' => 'Hi {{contact_name}}, welcome.',
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $dealership = Dealership::factory()->create();
        $dealerEmail = DealerEmail::factory()->create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'dealer_email_template_id' => $template->id,
            'customize_email' => false,
            'subject' => 'Custom Subject',
            'message' => 'Custom message',
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), 'Alice', 'track-1');

        expect($mail->subject)->toBe('Template Subject')
            ->and($mail->body)->toBe('Hi Alice, welcome.')
            ->and($mail->trackingId)->toBe('track-1');
    });

    it('uses the dealer email subject and body when customize_email is true', function (): void {
        $template = DealerEmailTemplate::factory()->create([
            'subject' => 'Template Subject',
            'body' => 'Template body for {{contact_name}}',
        ]);
        $dealerEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => $template->id,
            'customize_email' => true,
            'subject' => 'Custom Subject',
            'message' => 'Custom for {{contact_name}}',
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), 'Bob');

        expect($mail->subject)->toBe('Custom Subject')
            ->and($mail->body)->toBe('Custom for Bob');
    });

    it('falls back to dealer email subject and message when no template is set', function (): void {
        $dealerEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => null,
            'customize_email' => false,
            'subject' => 'No Template Subject',
            'message' => 'No template body',
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), 'Bob');

        expect($mail->subject)->toBe('No Template Subject')
            ->and($mail->body)->toBe('No template body');
    });

    it('builds the envelope with user from address, tags and metadata', function (): void {
        $user = User::factory()->create(['email' => 'sender@example.com', 'name' => 'Sender']);
        $this->actingAs($user);
        $dealership = Dealership::factory()->create();
        $dealerEmail = DealerEmail::factory()->create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'dealer_email_template_id' => null,
            'customize_email' => false,
            'subject' => 'Subject Line',
            'message' => 'Body',
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), 'Bob');
        $envelope = $mail->envelope();

        expect($envelope->subject)->toBe('Subject Line')
            ->and($envelope->from?->address)->toBe('sender@example.com')
            ->and($envelope->from?->name)->toBe('Sender from ARMP')
            ->and($envelope->tags)->toContain('dealer-email')
            ->and($envelope->metadata['dealer_email_id'])->toBe($dealerEmail->id)
            ->and($envelope->metadata['dealership_id'])->toBe($dealership->id)
            ->and($envelope->metadata['user_id'])->toBe($user->id);
    });

    it('renders the dealer-email markdown view with the message and user', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user);
        $dealerEmail = DealerEmail::factory()->create([
            'user_id' => $user->id,
            'dealer_email_template_id' => null,
            'customize_email' => false,
            'subject' => 'Sub',
            'message' => 'Body content',
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), null);
        $content = $mail->content();

        expect($content->markdown)->toBe('emails.dealer-email')
            ->and($content->with['message'])->toBe('Body content')
            ->and($content->with['user']->id)->toBe($user->id);
    });

    it('returns no attachments when there is no template and no custom pdf attachments', function (): void {
        $dealerEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => null,
            'customize_email' => false,
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), null);

        expect($mail->attachments())->toBe([]);
    });

    it('returns template pdf attachments when using template without customization', function (): void {
        $template = DealerEmailTemplate::factory()->create();
        $pdf = PdfAttachment::create(['file_name' => 'tpl.pdf', 'file_path' => 'tpl.pdf']);
        $template->pdfAttachments()->attach($pdf);

        $dealerEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => $template->id,
            'customize_email' => false,
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), null);

        expect($mail->attachments())->toHaveCount(1);
    });

    it('returns dealer email pdf attachments when present', function (): void {
        $dealerEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => null,
            'customize_email' => false,
        ]);
        $pdf = PdfAttachment::create(['file_name' => 'custom.pdf', 'file_path' => 'custom.pdf']);
        $dealerEmail->pdfAttachments()->attach($pdf);

        $mail = new DealerEmailMail($dealerEmail->fresh(), null);

        expect($mail->attachments())->toHaveCount(1);
    });

    it('falls back to template attachments when customizing but no custom attachments are added', function (): void {
        $template = DealerEmailTemplate::factory()->create();
        $pdf = PdfAttachment::create(['file_name' => 'fallback.pdf', 'file_path' => 'fallback.pdf']);
        $template->pdfAttachments()->attach($pdf);

        $dealerEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => $template->id,
            'customize_email' => true,
            'subject' => 'Customized',
            'message' => 'Customized body',
        ]);

        $mail = new DealerEmailMail($dealerEmail->fresh(), null);

        expect($mail->attachments())->toHaveCount(1);
    });
});

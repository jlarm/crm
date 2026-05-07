<?php

declare(strict_types=1);

use App\Jobs\SendDealerEmail;
use App\Mail\DealerEmailMail;
use App\Models\Contact;
use App\Models\DealerEmail;
use App\Models\Dealership;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

describe('SendDealerEmail job', function (): void {
    it('sends an email and logs a SentEmail record per recipient', function (): void {
        Mail::fake();

        $user = User::factory()->create();
        $this->actingAs($user);
        $dealership = Dealership::factory()->create();
        Contact::factory()->create([
            'dealership_id' => $dealership->id,
            'email' => 'contact@dealer.test',
            'name' => 'Jane Doe',
        ]);

        $dealerEmail = DealerEmail::factory()->create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'dealer_email_template_id' => null,
            'customize_email' => true,
            'subject' => 'Hello',
            'message' => 'Body',
            'recipients' => ['contact@dealer.test', 'no-contact@example.com'],
            'last_sent' => null,
        ]);

        (new SendDealerEmail($dealerEmail))->handle();

        Mail::assertSent(DealerEmailMail::class, 2);
        expect(SentEmail::count())->toBe(2)
            ->and($dealerEmail->fresh()->last_sent)->not->toBeNull();
    });

    it('returns early when there are no recipients', function (): void {
        Mail::fake();

        $dealerEmail = DealerEmail::factory()->create([
            'dealer_email_template_id' => null,
            'customize_email' => true,
            'subject' => 'X',
            'message' => 'Y',
            'recipients' => [],
            'last_sent' => null,
        ]);

        (new SendDealerEmail($dealerEmail))->handle();

        Mail::assertNothingSent();
        expect(SentEmail::count())->toBe(0)
            ->and($dealerEmail->fresh()->last_sent)->toBeNull();
    });

    it('logs the outer catch when saving the dealer email fails after sending', function (): void {
        Mail::fake();

        $user = User::factory()->create();
        $this->actingAs($user);
        $dealership = Dealership::factory()->create();

        $dealerEmail = DealerEmail::factory()->create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'dealer_email_template_id' => null,
            'customize_email' => true,
            'subject' => 'Hello',
            'message' => 'Body',
            'recipients' => ['contact@dealer.test'],
            'last_sent' => null,
        ]);

        // Make the model throw on its final save() to trigger the outer catch
        // (lines 96..97 of SendDealerEmail).
        DealerEmail::saving(function (DealerEmail $email): void {
            if ($email->last_sent !== null) {
                throw new RuntimeException('save blew up');
            }
        });

        Illuminate\Support\Facades\Log::shouldReceive('info')->andReturnNull();
        Illuminate\Support\Facades\Log::shouldReceive('error')
            ->atLeast()->once();

        (new SendDealerEmail($dealerEmail))->handle();

        // Inner send still worked.
        Mail::assertSent(DealerEmailMail::class, 1);
        expect(SentEmail::count())->toBe(1);
    });

    it('logs an error but continues when sending an individual email fails', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user);
        $dealership = Dealership::factory()->create();

        $dealerEmail = DealerEmail::factory()->create([
            'user_id' => $user->id,
            'dealership_id' => $dealership->id,
            'dealer_email_template_id' => null,
            'customize_email' => true,
            'subject' => 'Hello',
            'message' => 'Body',
            'recipients' => ['fail@example.com'],
            'last_sent' => null,
        ]);

        // Force Mail::to(...)->send(...) to throw to exercise inner catch (lines 84-89).
        Mail::shouldReceive('to')->andReturnUsing(function (): never {
            throw new RuntimeException('SMTP boom');
        });

        Illuminate\Support\Facades\Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($message, $context) => $message === 'Failed to send dealer email'
                && ($context['recipient'] ?? null) === 'fail@example.com');

        (new SendDealerEmail($dealerEmail))->handle();

        // Outer flow continues: SentEmail created before send and last_sent updated.
        expect(SentEmail::count())->toBe(1)
            ->and($dealerEmail->fresh()->last_sent)->not->toBeNull();
    });
});

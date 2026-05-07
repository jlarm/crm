<?php

declare(strict_types=1);

use App\Enum\ReminderFrequency;
use App\Jobs\SendDealerEmail;
use App\Models\DealerEmail;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
    Bus::fake();
    $user = User::factory()->create();
    test()->actingAs($user);
});

it('dispatches SendDealerEmail when frequency is Once and start date is today', function (): void {
    DealerEmail::factory()->active()->for(User::first())->create([
        'frequency' => ReminderFrequency::Once,
        'start_date' => now()->startOfDay(),
    ]);

    Bus::assertDispatched(SendDealerEmail::class);
});

it('does not dispatch SendDealerEmail when frequency is Once but start date is not today', function (): void {
    DealerEmail::factory()->active()->for(User::first())->create([
        'frequency' => ReminderFrequency::Once,
        'start_date' => now()->addDays(2),
    ]);

    Bus::assertNotDispatched(SendDealerEmail::class);
});

it('does not dispatch SendDealerEmail when frequency is not Once', function (): void {
    DealerEmail::factory()->active()->for(User::first())->create([
        'frequency' => ReminderFrequency::Weekly,
        'start_date' => now()->startOfDay(),
    ]);

    Bus::assertNotDispatched(SendDealerEmail::class);
});

it('deletes the previous attachment when attachment is changed on update', function (): void {
    Storage::disk('public')->put('attachments/old.pdf', 'old');
    Storage::disk('public')->put('attachments/new.pdf', 'new');

    $email = DealerEmail::factory()->for(User::first())->create([
        'attachment' => 'attachments/old.pdf',
        'attachment_name' => 'old.pdf',
    ]);

    $email->update(['attachment' => 'attachments/new.pdf']);

    expect(Storage::disk('public')->exists('attachments/old.pdf'))->toBeFalse()
        ->and(Storage::disk('public')->exists('attachments/new.pdf'))->toBeTrue();
});

it('does not delete anything on update when attachment is unchanged', function (): void {
    Storage::disk('public')->put('attachments/keep.pdf', 'data');

    $email = DealerEmail::factory()->for(User::first())->create([
        'attachment' => 'attachments/keep.pdf',
        'attachment_name' => 'keep.pdf',
    ]);

    $email->update(['subject' => 'Updated subject']);

    expect(Storage::disk('public')->exists('attachments/keep.pdf'))->toBeTrue();
});

it('deletes the attachment file when the dealer email is deleted', function (): void {
    Storage::disk('public')->put('attachments/bye.pdf', 'data');
    $email = DealerEmail::factory()->for(User::first())->create([
        'attachment' => 'attachments/bye.pdf',
        'attachment_name' => 'bye.pdf',
    ]);

    $email->delete();

    expect(Storage::disk('public')->exists('attachments/bye.pdf'))->toBeFalse();
});

it('does not attempt to delete on destroy when attachment is null', function (): void {
    Storage::disk('public')->put('attachments/other.pdf', 'data');
    $email = DealerEmail::factory()->for(User::first())->create(['attachment' => null]);

    $email->delete();

    expect(Storage::disk('public')->exists('attachments/other.pdf'))->toBeTrue();
});

<?php

declare(strict_types=1);

use App\Enum\EmailDirection;
use App\Enum\EmailStatus;
use App\Jobs\SendDealershipEmail;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\Progress;
use App\Models\ProgressCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    Queue::fake();
    Storage::fake('local');

    $this->rep = User::factory()->create(['name' => 'Rita Rep', 'email' => 'rita@armp.test']);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->rep->id]);
    $this->contact = Contact::factory()->create(['dealership_id' => $this->dealership->id, 'email' => 'gm@smithford.test']);
    $this->emailCategory = ProgressCategory::factory()->create(['name' => 'Email']);
});

describe('DealershipEmailController store', function () {
    it('saves the email from the rep to the contact and queues it', function () {
        actingAs($this->rep);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Renewal options',
            'body' => "Hi Pat,\n\nHere are the options.",
        ])->assertRedirect()->assertSessionHas('success');

        $email = DealershipEmail::sole();
        expect($email->dealership_id)->toBe($this->dealership->id)
            ->and($email->direction)->toBe(EmailDirection::Outbound)
            ->and($email->status)->toBe(EmailStatus::Queued)
            ->and($email->from_email)->toBe('rita@armp.test')
            ->and($email->from_name)->toBe('Rita Rep')
            ->and($email->to_email)->toBe('gm@smithford.test')
            ->and($email->message_id)->toEndWith('@armp.test')
            ->and($email->reply_token)->not->toBeNull();

        Queue::assertPushed(SendDealershipEmail::class, fn (SendDealershipEmail $job): bool => $job->email->is($email));
    });

    it('saves the message as sanitized HTML with a plain-text copy', function () {
        actingAs($this->rep);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Renewal options',
            'body' => '<p>Hi <strong>Pat</strong></p><script>alert(1)</script><ul><li><p>Option A</p></li></ul>',
        ])->assertSessionHasNoErrors();

        $email = DealershipEmail::sole();
        expect($email->body_html)->toBe('<p>Hi <strong>Pat</strong></p><ul><li>Option A</li></ul>')
            ->and($email->body_text)->toBe('Hi Pat

- Option A');
    });

    it('rejects an empty message from the editor', function () {
        actingAs($this->rep);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Hello',
            'body' => '<p></p>',
        ])->assertSessionHasErrors(['body' => 'Write a message.']);

        expect(DealershipEmail::count())->toBe(0);
    });

    it('logs the email as an Email touchpoint', function () {
        actingAs($this->rep);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Renewal options',
            'body' => 'Hi',
        ]);

        $progress = Progress::sole();
        expect($progress->progress_category_id)->toBe($this->emailCategory->id)
            ->and($progress->contact_id)->toBe($this->contact->id)
            ->and($progress->user_id)->toBe($this->rep->id)
            ->and($progress->details)->toBe('Sent from CRM: Renewal options');
    });

    it('stores attachments privately', function () {
        actingAs($this->rep);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Brochure',
            'body' => 'Attached.',
            'attachments' => [UploadedFile::fake()->create('brochure.pdf', 200, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $attachment = DealershipEmail::sole()->attachments()->sole();
        expect($attachment->filename)->toBe('brochure.pdf');
        Storage::disk('local')->assertExists($attachment->path);
    });

    it('threads a reply under the email that started the conversation', function () {
        actingAs($this->rep);
        $first = DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id]);
        $theirReply = DealershipEmail::factory()->inbound()->create(['dealership_id' => $this->dealership->id, 'parent_id' => $first->id]);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Re: Renewal options',
            'body' => 'Thanks!',
            'parent_id' => $theirReply->id,
        ])->assertSessionHasNoErrors();

        $sent = DealershipEmail::latest('id')->first();
        expect($sent->parent_id)->toBe($first->id)
            ->and($sent->in_reply_to)->toBe($theirReply->message_id);
    });

    it('rejects a contact from another dealership', function () {
        actingAs($this->rep);
        $stranger = Contact::factory()->create(['email' => 'other@example.test']);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $stranger->id,
            'subject' => 'Hello',
            'body' => 'Hi',
        ])->assertSessionHasErrors('contact_id');

        expect(DealershipEmail::count())->toBe(0);
        Queue::assertNothingPushed();
    });

    it('rejects a contact without an email address', function () {
        actingAs($this->rep);
        $this->contact->update(['email' => null]);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Hello',
            'body' => 'Hi',
        ])->assertSessionHasErrors('contact_id');
    });

    it('rejects a reply to an email from another dealership', function () {
        actingAs($this->rep);
        $elsewhere = DealershipEmail::factory()->create();

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Re: Hello',
            'body' => 'Hi',
            'parent_id' => $elsewhere->id,
        ])->assertSessionHasErrors('parent_id');
    });

    it('rejects attachments over 10 MB in total', function () {
        actingAs($this->rep);

        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Files',
            'body' => 'Attached.',
            'attachments' => [
                UploadedFile::fake()->create('one.pdf', 6 * 1024, 'application/pdf'),
                UploadedFile::fake()->create('two.pdf', 6 * 1024, 'application/pdf'),
            ],
        ])->assertSessionHasErrors('attachments');

        expect(DealershipEmail::count())->toBe(0);
    });

    it('redirects guests to the login page', function () {
        post(route('dealerships.emails.store', $this->dealership), [
            'contact_id' => $this->contact->id,
            'subject' => 'Hello',
            'body' => 'Hi',
        ])->assertRedirect(route('login'));
    });
});

describe('DealershipEmailAttachmentController show', function () {
    it('downloads an attachment for its dealership only', function () {
        actingAs($this->rep);
        $email = DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id]);
        Storage::disk('local')->put('dealership-emails/1/quote.pdf', 'pdf');
        $attachment = $email->attachments()->create(['filename' => 'quote.pdf', 'path' => 'dealership-emails/1/quote.pdf', 'mime_type' => 'application/pdf', 'size' => 3]);

        get(route('dealerships.emails.attachments.show', [$this->dealership, $attachment]))
            ->assertOk()
            ->assertDownload('quote.pdf');

        get(route('dealerships.emails.attachments.show', [Dealership::factory()->create(['user_id' => $this->rep->id]), $attachment]))
            ->assertNotFound();
    });
});

describe('Dealership show emailThreads', function () {
    it('groups emails into threads, newest conversation first', function () {
        actingAs($this->rep);
        $older = DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id, 'subject' => 'Older', 'created_at' => now()->subDays(3)]);
        $newer = DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id, 'subject' => 'Newer', 'created_at' => now()->subDays(2)]);
        DealershipEmail::factory()->inbound()->create([
            'dealership_id' => $this->dealership->id,
            'parent_id' => $older->id,
            'subject' => 'Re: Older',
            'created_at' => now()->subDay(),
        ]);
        DealershipEmail::factory()->create(['subject' => 'Other dealership']);

        get(route('dealerships.show', $this->dealership))
            ->assertInertia(fn ($page) => $page
                ->missing('emailThreads')
                ->loadDeferredProps(fn ($reload) => $reload
                    ->has('emailThreads', 2)
                    ->where('emailThreads.0.0.subject', 'Older')
                    ->where('emailThreads.0.1.subject', 'Re: Older')
                    ->where('emailThreads.0.1.direction', 'inbound')
                    ->where('emailThreads.1.0.subject', 'Newer')
                )
            );
    });
});

describe('Dealership activity feed', function () {
    it('shows replies from contacts', function () {
        actingAs($this->rep);
        DealershipEmail::factory()->inbound()->create([
            'dealership_id' => $this->dealership->id,
            'contact_id' => $this->contact->id,
            'subject' => 'Re: Renewal options',
            'body_text' => 'Sounds good.',
        ]);

        $this->getJson(route('dealerships.activities.index', $this->dealership))
            ->assertOk()
            ->assertJsonFragment([
                'icon' => 'email',
                'title' => sprintf('%s replied: Re: Renewal options', $this->contact->name),
                'description' => 'Sounds good.',
            ]);
    });
});

describe('DealershipEmailController markRead', function () {
    it('marks the replies in that thread as read, and only that thread', function () {
        actingAs($this->rep);
        $thread = DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id]);
        $reply = DealershipEmail::factory()->inbound()->create(['dealership_id' => $this->dealership->id, 'parent_id' => $thread->id]);
        $otherThread = DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id]);
        $otherReply = DealershipEmail::factory()->inbound()->create(['dealership_id' => $this->dealership->id, 'parent_id' => $otherThread->id]);

        $this->patch(route('dealerships.emails.read', [$this->dealership, $thread]))->assertRedirect();

        expect($reply->fresh()->read_at)->not->toBeNull()
            ->and($otherReply->fresh()->read_at)->toBeNull();
    });

    it('returns 404 for an email from another dealership', function () {
        actingAs($this->rep);
        $elsewhere = DealershipEmail::factory()->create();

        $this->patch(route('dealerships.emails.read', [$this->dealership, $elsewhere]))->assertNotFound();
    });

    it('shares the number of unread replies with the dealership page', function () {
        actingAs($this->rep);
        $thread = DealershipEmail::factory()->create(['dealership_id' => $this->dealership->id]);
        DealershipEmail::factory()->inbound()->count(2)->create(['dealership_id' => $this->dealership->id, 'parent_id' => $thread->id]);
        DealershipEmail::factory()->inbound()->create(['dealership_id' => $this->dealership->id, 'parent_id' => $thread->id, 'read_at' => now()]);
        DealershipEmail::factory()->inbound()->create(['parent_id' => null]);

        get(route('dealerships.show', $this->dealership))
            ->assertInertia(fn ($page) => $page->where('unreadReplyCount', 2));
    });
});

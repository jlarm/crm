<?php

declare(strict_types=1);

use App\Enum\EmailDirection;
use App\Enum\EmailStatus;
use App\Mail\ForwardedReplyMail;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\post;

beforeEach(function () {
    Mail::fake();
    Storage::fake('local');
    config()->set('services.mailgun.webhook_signing_key', 'inbound-key');

    $this->rep = User::factory()->create(['email' => 'rita@armp.test']);
    $this->dealership = Dealership::factory()->create(['user_id' => $this->rep->id]);
    $this->contact = Contact::factory()->create(['dealership_id' => $this->dealership->id, 'email' => 'pat@smithford.test']);
    $this->sent = DealershipEmail::factory()->create([
        'dealership_id' => $this->dealership->id,
        'contact_id' => $this->contact->id,
        'user_id' => $this->rep->id,
        'reply_token' => 'tok123',
        'message_id' => 'sent-1@armp.test',
    ]);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function inboundReply(array $overrides = []): array
{
    $timestamp = (string) now()->timestamp;
    $token = 'random-token';

    return [
        'timestamp' => $timestamp,
        'token' => $token,
        'signature' => hash_hmac('sha256', $timestamp.$token, 'inbound-key'),
        'recipient' => 'reply+tok123@reply.armp.test',
        'sender' => 'pat@smithford.test',
        'from' => 'Pat Smith <Pat@SmithFord.test>',
        'subject' => 'Re: Renewal options',
        'body-plain' => "Sounds good.\n\n> On Monday Rita wrote:",
        'stripped-text' => 'Sounds good.',
        'Message-Id' => '<reply-1@smithford.test>',
        'In-Reply-To' => '<sent-1@armp.test>',
        ...$overrides,
    ];
}

it('saves the reply on the thread and forwards it to the rep', function () {
    post(route('mailgun.inbound'), inboundReply())->assertOk();

    $reply = DealershipEmail::where('direction', EmailDirection::Inbound)->sole();
    expect($reply->parent_id)->toBe($this->sent->id)
        ->and($reply->dealership_id)->toBe($this->dealership->id)
        ->and($reply->contact_id)->toBe($this->contact->id)
        ->and($reply->user_id)->toBe($this->rep->id)
        ->and($reply->from_email)->toBe('pat@smithford.test')
        ->and($reply->from_name)->toBe('Pat Smith')
        ->and($reply->body_text)->toBe('Sounds good.')
        ->and($reply->message_id)->toBe('reply-1@smithford.test')
        ->and($reply->status)->toBe(EmailStatus::Received);

    Mail::assertQueued(ForwardedReplyMail::class, fn (ForwardedReplyMail $mail): bool => $mail->hasTo('rita@armp.test')
        && $mail->hasFrom('crm@m.autorisknow.com')
        && $mail->hasReplyTo('pat@smithford.test')
        && $mail->mailer === 'dealership'
        && $mail->reply->is($reply));
});

it('stores attachments sent with the reply', function () {
    post(route('mailgun.inbound'), inboundReply([
        'attachment-count' => 1,
        'attachment-1' => UploadedFile::fake()->create('signed.pdf', 50, 'application/pdf'),
    ]))->assertOk();

    $attachment = DealershipEmail::where('direction', EmailDirection::Inbound)->sole()->attachments()->sole();
    expect($attachment->filename)->toBe('signed.pdf');
    Storage::disk('local')->assertExists($attachment->path);
});

it('finds the thread from In-Reply-To when the token is missing', function () {
    post(route('mailgun.inbound'), inboundReply(['recipient' => 'rita@armp.test']))->assertOk();

    expect(DealershipEmail::where('direction', EmailDirection::Inbound)->sole()->parent_id)->toBe($this->sent->id);
});

it('ignores a reply Mailgun posts twice', function () {
    post(route('mailgun.inbound'), inboundReply())->assertOk();
    post(route('mailgun.inbound'), inboundReply())->assertOk();

    expect(DealershipEmail::where('direction', EmailDirection::Inbound)->count())->toBe(1);
    Mail::assertQueuedCount(1);
});

it('drops mail that does not belong to a thread', function () {
    post(route('mailgun.inbound'), inboundReply([
        'recipient' => 'reply+unknown@reply.armp.test',
        'In-Reply-To' => '<nope@elsewhere.test>',
    ]))->assertOk();

    expect(DealershipEmail::where('direction', EmailDirection::Inbound)->count())->toBe(0);
    Mail::assertNothingQueued();
});

it('rejects a bad signature', function () {
    post(route('mailgun.inbound'), inboundReply(['signature' => 'forged']))->assertUnauthorized();

    expect(DealershipEmail::where('direction', EmailDirection::Inbound)->count())->toBe(0);
});

it('rejects a replayed request', function () {
    $timestamp = (string) now()->subHour()->timestamp;

    post(route('mailgun.inbound'), inboundReply([
        'timestamp' => $timestamp,
        'signature' => hash_hmac('sha256', $timestamp.'random-token', 'inbound-key'),
    ]))->assertUnauthorized();
});

it('rejects everything when no signing key is configured', function () {
    config()->set('services.mailgun.webhook_signing_key', null);

    post(route('mailgun.inbound'), inboundReply())->assertUnauthorized();
});

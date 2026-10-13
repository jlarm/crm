<?php

declare(strict_types=1);

use App\Enum\EmailFailureReason;
use App\Enum\EmailStatus;
use App\Jobs\SendDealershipEmail;
use App\Mail\DealershipEmailMail;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config()->set('services.mailgun.inbound_domain', 'reply.armp.test');

    $this->rep = User::factory()->create(['name' => 'Rita Rep', 'email' => 'rita@armp.test']);
    $this->email = DealershipEmail::factory()->queued()->create([
        'user_id' => $this->rep->id,
        'from_email' => 'rita@armp.test',
        'from_name' => 'Rita Rep',
        'to_email' => 'gm@smithford.test',
        'reply_token' => 'abc123',
        'message_id' => 'thread-1@armp.test',
    ]);
});

it('sends from the rep, BCCs the rep and routes replies to the thread address', function () {
    Mail::fake();

    (new SendDealershipEmail($this->email))->handle();

    Mail::assertSent(DealershipEmailMail::class, function (DealershipEmailMail $mail): bool {
        return $mail->hasFrom('rita@armp.test', 'Rita Rep')
            && $mail->hasTo('gm@smithford.test')
            && $mail->hasBcc('rita@armp.test')
            && $mail->hasReplyTo('reply+abc123@reply.armp.test')
            && $mail->hasMetadata('dealership_email_id', (string) $this->email->id)
            && $mail->headers()->messageId === 'thread-1@armp.test'
            && $mail->mailer === 'dealership';
    });

    expect($this->email->fresh()->status)->toBe(EmailStatus::Sent)
        ->and($this->email->fresh()->sent_at)->not->toBeNull();
});

it('lets replies go straight to the rep when no inbound domain is set', function () {
    Mail::fake();
    config()->set('services.mailgun.inbound_domain', null);

    (new SendDealershipEmail($this->email))->handle();

    Mail::assertSent(DealershipEmailMail::class, fn (DealershipEmailMail $mail): bool => $mail->hasReplyTo('rita@armp.test'));
});

it('does not send an email twice', function () {
    Mail::fake();
    $this->email->update(['status' => EmailStatus::Sent]);

    (new SendDealershipEmail($this->email))->handle();

    Mail::assertNothingSent();
});

it('marks the email as not sent when every attempt fails', function () {
    (new SendDealershipEmail($this->email))->failed(new RuntimeException('Connection refused'));

    $email = $this->email->fresh();
    expect($email->status)->toBe(EmailStatus::Failed)
        ->and($email->status_reason)->toBe(EmailFailureReason::SendError)
        ->and($email->status_detail)->toBe('Connection refused');
});

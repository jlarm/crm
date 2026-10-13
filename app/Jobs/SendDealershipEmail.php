<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\EmailFailureReason;
use App\Enum\EmailStatus;
use App\Mail\DealershipEmailMail;
use App\Models\DealershipEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Hands a rep's email to the mail service. Mailgun's webhooks take it from there.
 */
final class SendDealershipEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 120];

    public function __construct(public readonly DealershipEmail $email) {}

    public function handle(): void
    {
        if ($this->email->status !== EmailStatus::Queued) {
            return;
        }

        $this->email->load(['user', 'contact', 'attachments']);

        Mail::mailer('dealership')->send(new DealershipEmailMail($this->email));

        $this->email->update([
            'status' => EmailStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->email->update([
            'status' => EmailStatus::Failed,
            'status_reason' => EmailFailureReason::SendError,
            'status_detail' => $exception === null ? null : mb_substr($exception->getMessage(), 0, 1000),
            'failed_at' => now(),
        ]);
    }
}

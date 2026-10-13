<?php

declare(strict_types=1);

use App\Actions\Emails\ClassifyMailgunFailure;
use App\Enum\EmailFailureReason;

it('classifies a Mailgun failure', function (array $eventData, EmailFailureReason $expected) {
    expect((new ClassifyMailgunFailure)($eventData))->toBe($expected);
})->with([
    'mailbox full' => [
        ['reason' => 'generic', 'delivery-status' => ['code' => 552, 'message' => '5.2.2 The email account that you tried to reach is over quota.']],
        EmailFailureReason::MailboxFull,
    ],
    'temporary quota' => [
        ['severity' => 'temporary', 'reason' => 'generic', 'delivery-status' => ['code' => 452, 'message' => '4.2.2 Mailbox full']],
        EmailFailureReason::MailboxFull,
    ],
    'unknown user' => [
        ['reason' => 'bounce', 'delivery-status' => ['code' => 550, 'message' => '5.1.1 The email account that you tried to reach does not exist.']],
        EmailFailureReason::AddressNotFound,
    ],
    'bare 550' => [
        ['reason' => 'generic', 'delivery-status' => ['code' => 550, 'message' => 'Requested action not taken']],
        EmailFailureReason::AddressNotFound,
    ],
    'spam block' => [
        ['reason' => 'generic', 'delivery-status' => ['code' => 554, 'message' => '5.7.1 Message rejected as spam by Content Filtering.']],
        EmailFailureReason::Blocked,
    ],
    'policy block on 550' => [
        ['reason' => 'generic', 'delivery-status' => ['code' => 550, 'message' => '5.7.1 Service unavailable; client host blocked using Spamhaus']],
        EmailFailureReason::Blocked,
    ],
    'previously bounced' => [
        ['reason' => 'suppress-bounce', 'delivery-status' => ['code' => 605, 'description' => 'Not delivering to previously bounced address']],
        EmailFailureReason::Suppressed,
    ],
    'unrecognised' => [
        ['reason' => 'generic', 'delivery-status' => ['code' => 421, 'message' => 'Try again later']],
        EmailFailureReason::Other,
    ],
]);

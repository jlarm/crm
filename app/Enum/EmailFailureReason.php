<?php

declare(strict_types=1);

namespace App\Enum;

enum EmailFailureReason: string
{
    case MailboxFull = 'mailbox_full';
    case AddressNotFound = 'address_not_found';
    case Blocked = 'blocked';
    case Suppressed = 'suppressed';
    case SpamComplaint = 'spam_complaint';
    case SendError = 'send_error';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MailboxFull => 'Mailbox full',
            self::AddressNotFound => "Address doesn't exist",
            self::Blocked => "Blocked by the recipient's server (spam filter or policy)",
            self::Suppressed => 'Not attempted: this address bounced or reported spam before',
            self::SpamComplaint => 'The recipient marked this email as spam',
            self::SendError => "Couldn't hand the email to the mail service",
            self::Other => 'Rejected by the recipient\'s server',
        };
    }
}

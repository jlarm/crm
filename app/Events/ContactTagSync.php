<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Contact;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactTagSync
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Contact $contact, public ?string $actingUserName = null) {}
}

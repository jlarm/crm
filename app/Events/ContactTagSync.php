<?php

namespace App\Events;

use App\Models\Contact;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactTagSync
{
    use Dispatchable, SerializesModels;

    /**
     * The contact instance.
     *
     * @var \App\Models\Contact
     */
    public $contact;

    /**
     * The name of the user performing the action, if available.
     *
     * @var string|null
     */
    public $actingUserName;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Contact  $contact
     * @param  string|null $actingUserName
     * @return void
     */
    public function __construct(Contact $contact, ?string $actingUserName = null)
    {
        $this->contact = $contact;
        $this->actingUserName = $actingUserName;
    }
}

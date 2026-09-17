<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Contact;

class ContactObserver
{
    /**
     * A newly created contact becomes the dealership's only primary contact.
     */
    public function created(Contact $model): void
    {
        $model->dealership?->contacts()
            ->whereKeyNot($model->id)
            ->update(['primary_contact' => false]);
    }
}

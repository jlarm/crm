<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\ContactTagSync;
use App\Models\Contact;
use App\Models\Dealership;
use Exception;
use Illuminate\Support\Facades\Log;
use Spatie\MailcoachSdk\Exceptions\ResourceNotFound;
use Spatie\MailcoachSdk\Facades\Mailcoach;

class ContactObserver
{
    public static bool $syncMailcoach = true;

    /**
     * Handle the Contact "created" event.
     */
    public function created(Contact $model): void
    {
        $dealer = Dealership::where('id', $model->dealership_id)->first();
        // Ensure dealer exists before trying to update contacts, to prevent error if dealer is null
        if ($dealer) {
            $dealer->contacts()->where('id', '!=', $model->id)->update(['primary_contact' => false]);
        }

        if (! self::$syncMailcoach) {
            return;
        }

        $this->handleSavedEvent($model);

        $actingUserName = auth()->user()?->name; // Safely access user name
        event(new ContactTagSync($model, $actingUserName));
    }

    public function updated(Contact $model): void
    {
        if (! self::$syncMailcoach) {
            return;
        }

        $this->handleSavedEvent($model);

        $actingUserName = auth()->user()?->name; // Safely access user name
        event(new ContactTagSync($model, $actingUserName));
    }

    /**
     * Handle the Contact "deleted" event.
     */
    public function deleted(Contact $model): void
    {
        if (! self::$syncMailcoach || $model->dealership === null || $model->email === null) {
            return;
        }

        /** @var \Spatie\MailcoachSdk\Resources\EmailList $list */
        $list = Mailcoach::emailList($model->dealership->getListType()); // @phpstan-ignore staticMethod.notFound
        $sub = $list->subscriber($model->email);
        if ($sub) {
            $sub->delete();
        }
    }

    /**
     * Handle the Mailcoach subscriber creation/update logic.
     */
    protected function handleSavedEvent(Contact $model): void
    {
        if (! $model->dealership) {
            Log::warning(sprintf('[ContactObserver] No dealership found for contact ID %s. Skipping Mailcoach sync.', $model->id));

            return;
        }

        $listUuid = null;

        try {
            Log::debug(sprintf('[ContactObserver] Processing contact ID %s, Email: %s, Dealership ID: %s', $model->id, $model->email, $model->dealership_id));

            $listUuid = $model->dealership->getListType();
            Log::debug(sprintf('[ContactObserver] Dealership getListType() for contact ID %s (Dealership ID: %s) returned: ', $model->id, $model->dealership_id).$listUuid);

            if ($listUuid === 'default_value') {
                Log::warning(sprintf("[ContactObserver] Dealership type for contact ID %s (Dealership ID: %s) resulted in 'default_value' for list UUID. Skipping Mailcoach sync.", $model->id, $model->dealership_id));

                return;
            }

            /** @var \Spatie\MailcoachSdk\Resources\EmailList $list */
            $list = Mailcoach::emailList($listUuid); // @phpstan-ignore staticMethod.notFound

            if (empty($model->email)) {
                Log::warning(sprintf('[ContactObserver] Empty email for contact ID %s. Skipping Mailcoach sync.', $model->id));

                return;
            }

            $subscriber = $list->subscriber($model->email);

            $name_parts = explode(' ', mb_trim($model->name ?? ''));
            $first_name = $name_parts[0];
            $last_name = ''; // Initialize last_name

            if (count($name_parts) > 1) {
                $last_name = implode(' ', array_slice($name_parts, 1));
            }

            if ($subscriber) {
                /** @phpstan-ignore staticMethod.notFound */
                Mailcoach::updateSubscriber(
                    subscriberUuid: $subscriber->uuid,
                    attributes: [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                    ]
                );
                Log::info(sprintf('[ContactObserver] Updated Mailcoach subscriber for contact ID %s, Email: %s.', $model->id, $model->email));

                return;
            }

            /** @phpstan-ignore staticMethod.notFound */
            Mailcoach::createSubscriber(
                emailListUuid: $listUuid,
                attributes: [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $model->email,
                ]
            );
            Log::info(sprintf('[ContactObserver] Created Mailcoach subscriber for contact ID %s, Email: %s.', $model->id, $model->email));

        } catch (ResourceNotFound $e) {
            Log::error(sprintf('[ContactObserver] ResourceNotFound for contact ID %s. List UUID used: ', $model->id).($listUuid ?? 'unknown').'. Error: '.$e->getMessage(), ['exception' => $e, 'contact_id' => $model->id, 'dealership_id' => $model->dealership_id, 'email' => $model->email]);

            return;
        } catch (Exception $e) {
            Log::error(sprintf('[ContactObserver] Exception for contact ID %s. Error: ', $model->id).$e->getMessage(), ['exception' => $e, 'contact_id' => $model->id, 'dealership_id' => $model->dealership_id, 'email' => $model->email]);

            return;
        }
    }
}

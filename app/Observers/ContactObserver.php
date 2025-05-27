<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\Dealership;
use App\Events\ContactTagSync;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Illuminate\Support\Facades\Log;

class ContactObserver
{
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

        $this->handleSavedEvent($model);
        
        $actingUserName = auth()->user()?->name; // Safely access user name
        event(new ContactTagSync($model, $actingUserName));
    }

    public function updated(Contact $model): void
    {
        $this->handleSavedEvent($model);
        
        $actingUserName = auth()->user()?->name; // Safely access user name
        event(new ContactTagSync($model, $actingUserName));
    }

    /**
     * Handle the Contact "deleted" event.
     */
    public function deleted(Contact $model): void
    {
        $list = Mailcoach::emailList($model->dealership->getListType());
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
        if (!$model->dealership) {
            Log::warning("[ContactObserver] No dealership found for contact ID {$model->id}. Skipping Mailcoach sync.");
            return;
        }

        $listUuid = null; 

        try {
            Log::debug("[ContactObserver] Processing contact ID {$model->id}, Email: {$model->email}, Dealership ID: {$model->dealership_id}");
            
            $listUuid = $model->dealership->getListType();
            Log::debug("[ContactObserver] Dealership getListType() for contact ID {$model->id} (Dealership ID: {$model->dealership_id}) returned: " . $listUuid);

            if ($listUuid === 'default_value') {
                Log::warning("[ContactObserver] Dealership type for contact ID {$model->id} (Dealership ID: {$model->dealership_id}) resulted in 'default_value' for list UUID. Skipping Mailcoach sync.");
                return;
            }
            
            $list = Mailcoach::emailList($listUuid);

            if (empty($model->email)) {
                Log::warning("[ContactObserver] Empty email for contact ID {$model->id}. Skipping Mailcoach sync.");
                return;
            }

            $subscriber = $list->subscriber($model->email);
            
            $name_parts = explode(' ', trim($model->name ?? ''));
            $first_name = $name_parts[0] ?? '';
            $last_name = ''; // Initialize last_name

            if (count($name_parts) > 1) {
                $last_name = implode(' ', array_slice($name_parts, 1));
            }

            if ($subscriber) {
                Mailcoach::updateSubscriber(
                    subscriberUuid: $subscriber->uuid,
                    attributes: [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                    ]
                );
                Log::info("[ContactObserver] Updated Mailcoach subscriber for contact ID {$model->id}, Email: {$model->email}.");
                return;
            }

            Mailcoach::createSubscriber(
                emailListUuid: $listUuid, 
                attributes: [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $model->email,
                ]
            );
            Log::info("[ContactObserver] Created Mailcoach subscriber for contact ID {$model->id}, Email: {$model->email}.");

        } catch (\Spatie\MailcoachSdk\Exceptions\ResourceNotFound $e) {
            Log::error("[ContactObserver] ResourceNotFound for contact ID {$model->id}. List UUID used: " . ($listUuid ?? 'unknown') . ". Error: " . $e->getMessage(), ['exception' => $e, 'contact_id' => $model->id, 'dealership_id' => $model->dealership_id, 'email' => $model->email]);
            return;
        } catch(\Exception $e) {
            Log::error("[ContactObserver] Exception for contact ID {$model->id}. Error: " . $e->getMessage(), ['exception' => $e, 'contact_id' => $model->id, 'dealership_id' => $model->dealership_id, 'email' => $model->email]);
            return;
        }
    }
}

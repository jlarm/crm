<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\Dealership;
use App\Events\ContactTagSync;
use Spatie\MailcoachSdk\Facades\Mailcoach;

class ContactObserver
{
    /**
     * Handle the Contact "created" event.
     */
    public function created(Contact $model): void
    {
        $dealer = Dealership::where('id', $model->dealership_id)->first();
        $dealer->contacts()->where('id', '!=', $model->id)->update(['primary_contact' => false]);

        $this->handleSavedEvent($model);
        
        $actingUserName = auth()->check() ? auth()->user()->name : null;
        event(new ContactTagSync($model, $actingUserName));
    }

    public function updated(Contact $model): void
    {
        $this->handleSavedEvent($model);
        
        $actingUserName = auth()->check() ? auth()->user()->name : null;
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
            return;
        }

        try {
            $list = Mailcoach::emailList($model->dealership->getListType());

            if (empty($model->email)) {
                return;
            }

            $subscriber = $list->subscriber($model->email);
            
            if ($subscriber) {
                $name_parts = explode(' ', trim($model->name ?? ''));
                $first_name = $name_parts[0] ?? '';
                $last_name = $name_parts[1] ?? '';

                if (count($name_parts) > 1) {
                    $last_name = implode(' ', array_slice($name_parts, 1));
                }

                Mailcoach::updateSubscriber(
                    subscriberUuid: $subscriber->uuid,
                    attributes: [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                    ]
                );
                
                return;
            }

            $name_parts = explode(' ', trim($model->name ?? ''));
            $first_name = $name_parts[0] ?? '';
            $last_name = $name_parts[1] ?? '';

            if (count($name_parts) > 1) {
                $last_name = implode(' ', array_slice($name_parts, 1));
            }

            $sub = Mailcoach::createSubscriber(
                emailListUuid: $model->dealership->getListType(),
                attributes: [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $model->email,
                ]
            );
        } catch (\Spatie\MailcoachSdk\Exceptions\ResourceNotFound $e) {
            return;
        } catch(\Exception $e) {
            return;
        }
    }
}

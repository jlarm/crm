<?php

namespace App\Listeners;

use App\Events\ContactTagSync;
use Illuminate\Support\Facades\Log;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncContactTagsWithMailcoach implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\ContactTagSync  $event
     * @return void
     */
    public function handle(ContactTagSync $event): void
    {
        $model = $event->contact;

        // ==== BEGIN DEBUG LOGGING ====
        Log::debug('[SyncContactTagsWithMailcoach] Processing job.', [
            'contact_id' => $model->id,
            'contact_email' => $model->email,
            'event_actingUserName' => $event->actingUserName,
            'model_dealership_exists' => !is_null($model->dealership),
            'model_dealership_name' => $model->dealership ? $model->dealership->name : 'N/A (dealership is null)',
            'model_position' => $model->position,
        ]);
        // ==== END DEBUG LOGGING ====

        if (!$model->dealership) {
            Log::warning('[SyncContactTagsWithMailcoach] No dealership associated with contact. Aborting.', ['contact_id' => $model->id]);
            return;
        }

        try {
            $listUuid = $model->dealership->getListType();
            $list = Mailcoach::emailList($listUuid);

            if (empty($model->email)) {
                return;
            }

            $tags = [];
            if (!empty($model->position)) {
                $tags[] = $model->position;
            }

            if ($model->dealership->name) {
                $tags[] = $model->dealership->name;
            }

            // Use actingUserName from the event
            if (!empty($event->actingUserName)) {
                $tags[] = $event->actingUserName;
            }

            $freshModelInstance = $model->fresh(['tags']);

            if ($freshModelInstance) {
                $contactModelTags = $freshModelInstance->tags->pluck('name')->toArray();
                $tags = array_merge($tags, $contactModelTags);
            } else {
                Log::warning('Failed to refresh Contact model in SyncContactTagsWithMailcoach. Contact may have been deleted. Tags from relationship will not be included.', [
                    'contact_id' => $model->id,
                    'original_email' => $model->email 
                ]);
            }
            
            $tags = array_map(function($tag) {
                return preg_replace('/[^a-zA-Z0-9 -]/', '', $tag);
            }, $tags);
            $tags = array_unique(array_filter($tags));

            $subscriber = $list->subscriber($model->email);
            
            if ($subscriber) {
                try {
                    // Revert to addTags/removeTags as syncTags is not available on EmailList
                    $currentMailcoachTagsRaw = $subscriber->tags ?? [];
                    $currentMailcoachTags = [];
                    if (is_iterable($currentMailcoachTagsRaw)) {
                        foreach ($currentMailcoachTagsRaw as $tagObjectOrString) {
                            if (is_object($tagObjectOrString) && isset($tagObjectOrString->name)) {
                                $currentMailcoachTags[] = (string) $tagObjectOrString->name;
                            } elseif (is_string($tagObjectOrString)) {
                                $currentMailcoachTags[] = $tagObjectOrString;
                            }
                        }
                    }

                    $tagsToAdd = array_values(array_diff($tags, $currentMailcoachTags));
                    $tagsToRemove = array_values(array_diff($currentMailcoachTags, $tags));

                    if (!empty($tagsToAdd)) {
                        $subscriber->addTags($tagsToAdd);
                    }

                    if (!empty($tagsToRemove)) {
                        $subscriber->removeTags($tagsToRemove);
                    }

                    if (!empty($tagsToAdd) || !empty($tagsToRemove)) {
                        Log::info('Successfully synced tags for Mailcoach subscriber', [
                            'uuid' => $subscriber->uuid, 
                            'email' => $subscriber->email,
                            'added' => $tagsToAdd, 
                            'removed' => $tagsToRemove, 
                            'final_crm_state' => $tags
                        ]);
                    } else {
                        Log::debug('Mailcoach subscriber tags are already in sync.', ['uuid' => $subscriber->uuid, 'email' => $subscriber->email, 'current_tags' => $currentMailcoachTags]);
                    }

                } catch (\Spatie\MailcoachSdk\Exceptions\ResourceNotFound $e) {
                    Log::warning('Mailcoach resource not found during tag add/remove operation.', [
                        'subscriber_uuid' => $subscriber->uuid ?? 'unknown',
                        'email' => $model->email,
                        'error' => $e->getMessage()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error updating Mailcoach subscriber tags (add/remove): ' . $e->getMessage(), [
                        'subscriber_uuid' => $subscriber->uuid ?? 'unknown',
                        'email' => $model->email,
                        'exception_class' => get_class($e),
                    ]);
                }
            }
        } catch (\Spatie\MailcoachSdk\Exceptions\ResourceNotFound $e) {
            // This handles if $list = Mailcoach::emailList($listUuid) fails or $list->subscriber($model->email) fails to find subscriber when it's expected
            Log::warning('Mailcoach resource not found (likely list or initial subscriber lookup) in SyncContactTagsWithMailcoach.', [
                'contact_id' => $model->id,
                'email' => $model->email,
                'error' => $e->getMessage()
            ]);
            return;
        } catch(\Exception $e) {
            Log::error('Error in SyncContactTagsWithMailcoach: ' . $e->getMessage(), [
                'contact_id' => $model->id,
                'email' => $model->email,
                'exception_class' => get_class($e),
            ]);
            return;
        }
    }
}

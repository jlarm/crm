<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Contact;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dealership
 */
final class DealershipShowResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zipCode' => $this->zip_code,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'currentSolutionName' => $this->current_solution_name,
            'currentSolutionUse' => $this->current_solution_use,
            'status' => $this->status,
            'rating' => $this->rating,
            'stores' => $this->whenLoaded('stores', fn () => $this->stores->map(fn (Store $store): array => [
                'id' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'city' => $store->city,
                'state' => $store->state,
                'zipCode' => $store->zip_code,
                'phone' => $store->phone,
                'currentSolutionName' => $store->current_solution_name,
                'currentSolutionUse' => $store->current_solution_use,
            ])->all()),
            'contacts' => $this->whenLoaded('contacts', fn () => $this->contacts->map(fn (Contact $contact): array => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'position' => $contact->position,
                'linkedinLink' => $contact->linkedin_link,
                'primaryContact' => (bool) $contact->primary_contact,
            ])->all()),
            'users' => $this->whenLoaded('users', fn () => $this->users->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
            ])->all()),
            'opportunities' => $this->whenLoaded('opportunities', fn () => $this->opportunities->map(fn (Opportunity $o): array => [
                'id' => $o->id,
                'name' => $o->name,
                'stage' => $o->stage->value,
                'stageLabel' => $o->stage->getLabel(),
                'estimatedValue' => (float) ($o->estimated_value ?? 0),
                'probability' => $o->probability,
                'expectedCloseDate' => $o->expected_close_date?->format('Y-m-d'),
                'nextAction' => $o->next_action,
                'activities' => $o->relationLoaded('activities') ? $o->activities->map(fn (OpportunityActivity $a): array => [
                    'id' => $a->id,
                    'type' => $a->type->value,
                    'typeLabel' => $a->type->label(),
                    'details' => $a->details,
                    'occurredAt' => $a->occurred_at?->format('Y-m-d'),
                    'createdAt' => $a->created_at->format('Y-m-d'),
                    'user' => ['id' => $a->user->id, 'name' => $a->user->name],
                ])->all() : [],
            ])->all()),
        ];
    }
}

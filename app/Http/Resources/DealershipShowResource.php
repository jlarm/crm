<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DealershipShowResource extends JsonResource
{
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
            'stores' => $this->whenLoaded('stores', fn () => $this->stores->map(fn ($store) => [
                'id' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'city' => $store->city,
                'state' => $store->state,
                'zipCode' => $store->zip_code,
                'phone' => $store->phone,
                'currentSolutionName' => $store->current_solution_name,
                'currentSolutionUse' => $store->current_solution_use,
            ])),
            'contacts' => $this->whenLoaded('contacts', fn () => $this->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'position' => $contact->position,
                'linkedinLink' => $contact->linkedin_link,
                'primaryContact' => (bool) $contact->primary_contact,
            ])),
            'users' => $this->whenLoaded('users', fn () => $this->users->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])),
        ];
    }
}

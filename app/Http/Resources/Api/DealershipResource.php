<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DealershipResource extends JsonResource
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
            'zip_code' => $this->zip_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'type' => $this->type,
            'status' => $this->status,
            'rating' => $this->rating,
            'current_solution_name' => $this->current_solution_name,
            'current_solution_use' => $this->current_solution_use,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'stores' => StoreResource::collection($this->whenLoaded('stores')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
        ];
    }
}

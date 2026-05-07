<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contact
 */
final class ContactResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dealership_id' => $this->dealership_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'linkedin_link' => $this->linkedin_link,
            'primary_contact' => (bool) $this->primary_contact,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

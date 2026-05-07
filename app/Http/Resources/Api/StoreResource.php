<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Store
 */
final class StoreResource extends JsonResource
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
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'phone' => $this->phone,
            'current_solution_name' => $this->current_solution_name,
            'current_solution_use' => $this->current_solution_use,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

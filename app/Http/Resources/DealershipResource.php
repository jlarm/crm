<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DealershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'status' => $this->status,
            'statusLabel' => ucfirst((string) $this->status),
            'rating' => $this->rating,
            'ratingLabel' => ucfirst((string) $this->rating),
            'openTasksCount' => (int) ($this->open_tasks_count ?? 0),
        ];
    }
}

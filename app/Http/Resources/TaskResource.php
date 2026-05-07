<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
final class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type->value,
            'typeLabel' => $this->type->label(),
            'priority' => $this->priority->value,
            'priorityLabel' => $this->priority->label(),
            'dueDate' => $this->due_date?->toDateString(),
            'completedAt' => $this->completed_at?->toIso8601String(),
            'isCompleted' => $this->isCompleted(),
            'isOverdue' => $this->isOverdue(),
            'assignedTo' => $this->whenLoaded('user', fn () => [
                'id' => $this->resource->user->id,
                'name' => $this->resource->user->name,
            ]),
            'createdBy' => $this->whenLoaded('createdBy', fn () => [
                'id' => $this->resource->createdBy->id,
                'name' => $this->resource->createdBy->name,
            ]),
            'dealership' => $this->whenLoaded('dealership', fn () => $this->resource->dealership ? [
                'id' => $this->resource->dealership->id,
                'name' => $this->resource->dealership->name,
            ] : null),
            'contact' => $this->whenLoaded('contact', fn () => $this->resource->contact ? [
                'id' => $this->resource->contact->id,
                'name' => $this->resource->contact->name,
            ] : null),
            'dealershipId' => $this->dealership_id,
            'contactId' => $this->contact_id,
            'userId' => $this->user_id,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}

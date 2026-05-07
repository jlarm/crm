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
        /** @var Task $task */
        $task = $this->resource;

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
                'id' => $task->user->id,
                'name' => $task->user->name,
            ]),
            'createdBy' => $this->whenLoaded('createdBy', fn () => [
                'id' => $task->createdBy->id,
                'name' => $task->createdBy->name,
            ]),
            'dealership' => $this->whenLoaded('dealership', fn () => $task->dealership ? [
                'id' => $task->dealership->id,
                'name' => $task->dealership->name,
            ] : null),
            'contact' => $this->whenLoaded('contact', fn () => $task->contact ? [
                'id' => $task->contact->id,
                'name' => $task->contact->name,
            ] : null),
            'dealershipId' => $this->dealership_id,
            'contactId' => $this->contact_id,
            'userId' => $this->user_id,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}

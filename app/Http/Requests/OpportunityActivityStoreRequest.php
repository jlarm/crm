<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\ActivityType;
use App\Enum\OpportunityStage;
use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Opportunity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class OpportunityActivityStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The "apply" fields carry AI suggestions the rep approved; all of them are optional.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $opportunity = $this->route('opportunity');
        $dealershipId = $opportunity instanceof Opportunity ? $opportunity->dealership_id : 0;

        return [
            'type' => ['required', Rule::enum(ActivityType::class)],
            'details' => ['required', 'string'],
            'occurred_at' => ['nullable', 'date'],

            'apply' => ['nullable', 'array'],
            'apply.summary' => ['nullable', 'string', 'max:5000'],
            'apply.stage' => ['nullable', Rule::enum(OpportunityStage::class)],
            'apply.rating' => ['nullable', 'string', 'in:hot,warm,cold'],
            'apply.tasks' => ['nullable', 'array', 'max:20'],
            'apply.tasks.*.title' => ['required', 'string', 'max:255'],
            'apply.tasks.*.type' => ['required', Rule::enum(TaskType::class)],
            'apply.tasks.*.priority' => ['required', Rule::enum(TaskPriority::class)],
            'apply.tasks.*.due_date' => ['nullable', 'date'],
            'apply.tasks.*.contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')->where('dealership_id', $dealershipId)],
            'apply.contacts' => ['nullable', 'array', 'max:20'],
            'apply.contacts.*.name' => ['required', 'string', 'max:255'],
            'apply.contacts.*.position' => ['nullable', 'string', 'max:255'],
            'apply.contacts.*.email' => ['nullable', 'email', 'max:255'],
            'apply.contacts.*.phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Approved suggestions, normalised for ApplyCallNoteSuggestions.
     *
     * @return array{summary: string, stage: string|null, rating: string|null, tasks: list<array{title: string, type: string, priority: string, due_date: string|null, contact_id: int|null}>, contacts: list<array{name: string, position: string|null, email: string|null, phone: string|null}>}
     */
    public function suggestions(): array
    {
        $tasks = [];
        foreach ((array) $this->validated('apply.tasks', []) as $task) {
            if (! is_array($task)) {
                continue;
            }

            $tasks[] = [
                'title' => $this->stringOrNull($task['title'] ?? null) ?? '',
                'type' => $this->stringOrNull($task['type'] ?? null) ?? TaskType::FollowUp->value,
                'priority' => $this->stringOrNull($task['priority'] ?? null) ?? TaskPriority::Medium->value,
                'due_date' => $this->stringOrNull($task['due_date'] ?? null),
                'contact_id' => is_numeric($task['contact_id'] ?? null) ? (int) $task['contact_id'] : null,
            ];
        }

        $contacts = [];
        foreach ((array) $this->validated('apply.contacts', []) as $contact) {
            if (! is_array($contact)) {
                continue;
            }

            $contacts[] = [
                'name' => $this->stringOrNull($contact['name'] ?? null) ?? '',
                'position' => $this->stringOrNull($contact['position'] ?? null),
                'email' => $this->stringOrNull($contact['email'] ?? null),
                'phone' => $this->stringOrNull($contact['phone'] ?? null),
            ];
        }

        return [
            'summary' => $this->stringOrNull($this->validated('apply.summary')) ?? '',
            'stage' => $this->stringOrNull($this->validated('apply.stage')),
            'rating' => $this->stringOrNull($this->validated('apply.rating')),
            'tasks' => $tasks,
            'contacts' => $contacts,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}

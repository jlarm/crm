<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\TaskPriority;
use App\Enum\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(TaskType::class)],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'dealership_id' => ['nullable', 'integer', 'exists:dealerships,id'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
        ];
    }
}

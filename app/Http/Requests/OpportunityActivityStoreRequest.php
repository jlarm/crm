<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\ActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class OpportunityActivityStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ActivityType::class)],
            'details' => ['required', 'string'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}

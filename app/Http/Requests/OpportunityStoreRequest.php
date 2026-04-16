<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\OpportunityStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class OpportunityStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'stage' => ['required', Rule::enum(OpportunityStage::class)],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'next_action' => ['nullable', 'string', 'max:255'],
        ];
    }
}

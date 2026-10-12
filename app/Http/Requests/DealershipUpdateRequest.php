<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DealershipUpdateRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'current_solution_name' => ['nullable', 'string', 'max:255'],
            'current_solution_use' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'rating' => ['required', 'string', 'in:hot,warm,cold'],
            'user_ids' => ['array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reps paste bare domains ("smithford.com"); store them as full URLs.
     */
    protected function prepareForValidation(): void
    {
        $website = mb_trim((string) $this->input('website'));

        if ($website !== '' && preg_match('#^https?://#i', $website) !== 1) {
            $this->merge(['website' => 'https://'.$website]);
        }
    }
}

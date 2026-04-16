<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DealershipStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'string', 'in:Automotive,RV,Motorsports,Maritime,Association'],
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
}

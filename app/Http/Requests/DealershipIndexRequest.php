<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DealershipIndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'rating' => ['nullable', 'string', 'in:hot,warm,cold'],
            'type' => ['nullable', 'string', 'max:255'],
            'scope' => ['nullable', 'string', 'in:mine,all'],
            'include_imported' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'in:name,city,state,status,rating'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

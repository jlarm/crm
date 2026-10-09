<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DealershipImportConfirmRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'merge_duplicates' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

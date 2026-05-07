<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DealershipImportPreviewRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'mimetypes:text/csv,text/plain,application/csv', 'max:10240'],
            'default_status' => ['required', 'string', 'in:active,inactive'],
            'default_rating' => ['required', 'string', 'in:hot,warm,cold'],
            'default_type' => ['required', 'string', 'in:Automotive,RV,Motorsports,Maritime,Association'],
            'default_user_ids' => ['array'],
            'default_user_ids.*' => ['integer', 'exists:users,id'],
            'sync_mailcoach' => ['boolean'],
            'update_existing' => ['boolean'],
            'transactional' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

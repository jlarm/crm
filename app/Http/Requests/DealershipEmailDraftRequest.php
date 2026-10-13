<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Dealership;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DealershipEmailDraftRequest extends FormRequest
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
        $dealership = $this->route('dealership');
        $dealershipId = $dealership instanceof Dealership ? $dealership->id : 0;

        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('dealership_emails', 'id')->where('dealership_id', $dealershipId)],
            'contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')->where('dealership_id', $dealershipId)],
            // A new email has no thread to work from, so it needs the rep's notes.
            'notes' => ['nullable', 'required_without:parent_id', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.required_without' => 'Tell the AI what the email should say.',
        ];
    }
}

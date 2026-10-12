<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Dealership;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProgressStoreRequest extends FormRequest
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
            'progress_category_id' => ['required', 'integer', Rule::exists('progress_categories', 'id')],
            'contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')->where('dealership_id', $dealershipId)],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'details' => ['required', 'string', 'max:5000'],
        ];
    }
}

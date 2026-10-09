<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\OpportunityStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalesDashboardRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rep' => ['nullable', 'integer', 'exists:users,id'],
            'stage' => ['nullable', 'string', Rule::in(OpportunityStage::openValues())],
            'activity' => ['nullable', 'string', 'in:quiet'],
            'closing' => ['nullable', 'string', 'in:this_month,overdue'],
            'deals_page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalised deal filters, safe to pass to ListOpenDeals and echo back to the page.
     *
     * @return array{rep: string, stage: string, activity: string, closing: string}
     */
    public function dealFilters(): array
    {
        return [
            'rep' => $this->string('rep')->toString(),
            'stage' => $this->string('stage')->toString(),
            'activity' => $this->string('activity')->toString(),
            'closing' => $this->string('closing')->toString(),
        ];
    }
}

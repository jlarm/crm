<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DealershipIndexRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
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

    public function scope(): string
    {
        return $this->string('scope')->toString() === 'all' ? 'all' : 'mine';
    }

    public function includeImported(): bool
    {
        return $this->boolean('include_imported');
    }

    /**
     * Normalised filter values, safe to pass to ListDealerships and echo back to the page.
     *
     * @return array{search: string, status: string, rating: string, type: string, scope: string, include_imported: string, sort: string, direction: string}
     */
    public function filters(): array
    {
        return [
            'search' => $this->string('search')->toString(),
            'status' => $this->string('status')->toString(),
            'rating' => $this->string('rating')->toString(),
            'type' => $this->string('type')->toString(),
            'scope' => $this->scope(),
            'include_imported' => $this->includeImported() ? '1' : '',
            'sort' => $this->string('sort')->toString(),
            'direction' => $this->string('direction')->toString() === 'desc' ? 'desc' : 'asc',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Http\Resources\DealershipResource;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListDealerships
{
    /**
     * @param  array{search?: string, status?: string, rating?: string, type?: string, scope?: string, include_imported?: string, sort?: string, direction?: string}  $filters
     * @return LengthAwarePaginator<int, array<mixed, mixed>>
     */
    public function __invoke(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Dealership::query();

        if (($filters['scope'] ?? 'mine') !== 'all') {
            $query->forUser($user);
        }

        if (($filters['include_imported'] ?? '') === '') {
            $query->whereNot('status', 'imported');
        }

        return $query
            ->search($this->value($filters, 'search'))
            ->withStatus($this->value($filters, 'status'))
            ->withRating($this->value($filters, 'rating'))
            ->withType($this->value($filters, 'type'))
            ->sortBy($this->value($filters, 'sort'), $filters['direction'] ?? 'asc')
            ->select('id', 'name', 'city', 'state', 'type', 'status', 'rating')
            ->withCount(['tasks as open_tasks_count' => fn (Builder $q) => $q->whereNull('completed_at')])
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Dealership $dealership): array => DealershipResource::make($dealership)->resolve());
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function value(array $filters, string $key): ?string
    {
        $value = $filters[$key] ?? '';

        return $value === '' ? null : $value;
    }
}

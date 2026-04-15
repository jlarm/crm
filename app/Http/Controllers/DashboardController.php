<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DealershipIndexRequest;
use App\Http\Resources\DealershipResource;
use App\Models\Dealership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(DealershipIndexRequest $request): Response
    {
        $scope = $request->input('scope');
        if (! in_array($scope, ['mine', 'all'], true)) {
            $scope = 'mine';
        }

        $status = $request->input('status');

        $applyFilters = function (Builder $query) use ($request, $scope, $status): void {
            if ($scope === 'mine') {
                $query->forUser($request->user());
            }

            $query->search($request->input('search'))
                ->withRating($request->input('rating'))
                ->withType($request->input('type'));

            if ($status) {
                $query->where('status', $status);
            }
        };

        $dealershipQuery = Dealership::query();
        $applyFilters($dealershipQuery);

        $dealerships = $dealershipQuery
            ->sortBy($request->input('sort'), $request->input('direction'))
            ->select('id', 'name', 'city', 'state', 'status', 'rating')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($dealership) => DealershipResource::make($dealership)->resolve());

        $typeOptionsQuery = Dealership::query();
        $typeOptions = $typeOptionsQuery
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->filter()
            ->values()
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => Str::headline($type),
            ])
            ->all();

        return Inertia::render('Dashboard', [
            'dealerships' => $dealerships,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'rating' => $request->input('rating', ''),
                'type' => $request->input('type', ''),
                'scope' => $scope,
                'sort' => $request->input('sort', ''),
                'direction' => $request->input('direction', 'asc'),
            ],
            'filterOptions' => [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ],
                'ratings' => [
                    ['value' => 'hot', 'label' => 'Hot'],
                    ['value' => 'warm', 'label' => 'Warm'],
                    ['value' => 'cold', 'label' => 'Cold'],
                ],
                'types' => $typeOptions,
            ],
        ]);
    }
}

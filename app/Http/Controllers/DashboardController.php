<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dealerships\BuildActivitySummary;
use App\Actions\Dealerships\BuildBookSummary;
use App\Actions\Dealerships\BuildPipelineSummary;
use App\Actions\Dealerships\ListDealerships;
use App\Actions\Dealerships\ListDealershipsGoingQuiet;
use App\Actions\Tasks\BuildTaskFormOptions;
use App\Actions\Tasks\BuildTaskStats;
use App\Actions\Tasks\ListUpcomingTasks;
use App\Http\Requests\DealershipIndexRequest;
use App\Models\Dealership;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(
        DealershipIndexRequest $request,
        ListDealerships $listDealerships,
        BuildTaskStats $buildTaskStats,
        ListUpcomingTasks $listUpcomingTasks,
        BuildTaskFormOptions $buildTaskFormOptions,
        BuildBookSummary $buildBookSummary,
        ListDealershipsGoingQuiet $listDealershipsGoingQuiet,
        BuildPipelineSummary $buildPipelineSummary,
        BuildActivitySummary $buildActivitySummary,
    ): Response {
        /** @var User $user */
        $user = $request->user();
        $filters = $request->filters();

        return Inertia::render('Dashboard', [
            'dealerships' => $listDealerships($user, $filters),
            'filters' => $filters,
            'filterOptions' => [
                'statuses' => Dealership::statusOptions(),
                'ratings' => Dealership::ratingOptions(),
                'types' => Dealership::typeOptions(),
            ],
            'taskStats' => $buildTaskStats($user),
            'upcomingTasks' => $listUpcomingTasks($user),
            'taskFormData' => $buildTaskFormOptions(),
            'bookSummary' => $buildBookSummary($user),
            'goingQuiet' => $listDealershipsGoingQuiet($user),
            'pipeline' => $buildPipelineSummary($user),
            'activity' => $buildActivitySummary($user),
        ]);
    }
}

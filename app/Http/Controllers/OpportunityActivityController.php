<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Opportunities\ApplyCallNoteSuggestions;
use App\Http\Requests\OpportunityActivityStoreRequest;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class OpportunityActivityController extends Controller
{
    public function store(
        OpportunityActivityStoreRequest $request,
        Dealership $dealership,
        Opportunity $opportunity,
        ApplyCallNoteSuggestions $applyCallNoteSuggestions,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $applyCallNoteSuggestions(
            $opportunity,
            $user,
            [
                'type' => $request->string('type')->toString(),
                'details' => $request->string('details')->toString(),
                'occurred_at' => $request->filled('occurred_at') ? $request->string('occurred_at')->toString() : null,
            ],
            $request->suggestions(),
        );

        return back()->with('success', 'Activity logged.');
    }

    public function destroy(Dealership $dealership, Opportunity $opportunity, OpportunityActivity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('success', 'Activity deleted.');
    }
}

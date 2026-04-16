<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\OpportunityActivityStoreRequest;
use App\Models\Dealership;
use App\Models\Opportunity;
use App\Models\OpportunityActivity;
use Illuminate\Http\RedirectResponse;

final class OpportunityActivityController extends Controller
{
    public function store(OpportunityActivityStoreRequest $request, Dealership $dealership, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->activities()->create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Activity logged.');
    }

    public function destroy(Dealership $dealership, Opportunity $opportunity, OpportunityActivity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('success', 'Activity deleted.');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\OpportunityStoreRequest;
use App\Http\Requests\OpportunityUpdateRequest;
use App\Models\Dealership;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;

final class DealershipOpportunityController extends Controller
{
    public function store(OpportunityStoreRequest $request, Dealership $dealership): RedirectResponse
    {
        $dealership->opportunities()->create($request->validated());

        return back()->with('success', 'Opportunity created.');
    }

    public function update(OpportunityUpdateRequest $request, Dealership $dealership, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->update($request->validated());

        return back()->with('success', 'Opportunity updated.');
    }

    public function destroy(Dealership $dealership, Opportunity $opportunity): RedirectResponse
    {
        $opportunity->delete();

        return back()->with('success', 'Opportunity deleted.');
    }
}

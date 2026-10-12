<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProgressStoreRequest;
use App\Models\Dealership;
use App\Models\Progress;
use Illuminate\Http\RedirectResponse;

/**
 * Touchpoints a rep had outside the CRM (calls, emails, meetings), logged by hand.
 */
final class DealershipProgressController extends Controller
{
    public function store(ProgressStoreRequest $request, Dealership $dealership): RedirectResponse
    {
        $dealership->progresses()->create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
        ]);

        return back()->with('success', 'Activity logged.');
    }

    public function destroy(Dealership $dealership, Progress $progress): RedirectResponse
    {
        abort_unless($progress->dealership_id === $dealership->id, 404);

        $progress->delete();

        return back()->with('success', 'Activity deleted.');
    }
}

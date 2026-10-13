<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Stars a dealership for the signed-in user, to keep it on their dashboard.
 */
final class DealershipStarController extends Controller
{
    public function store(Request $request, Dealership $dealership): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->starredDealerships()->syncWithoutDetaching([$dealership->id]);

        return back();
    }

    public function destroy(Request $request, Dealership $dealership): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->starredDealerships()->detach($dealership->id);

        return back();
    }
}

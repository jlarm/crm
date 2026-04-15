<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DealershipUpdateRequest;
use App\Http\Resources\DealershipShowResource;
use App\Models\Dealership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class DealershipController extends Controller
{
    public function show(Dealership $dealership): Response
    {
        $dealership->load([
            'users' => fn ($query) => $query->select('id', 'name'),
            'stores',
            'contacts',
        ]);

        return Inertia::render('Dealership/Show', [
            'dealership' => DealershipShowResource::make($dealership)->resolve(),
            'allUsers' => User::query()->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(DealershipUpdateRequest $request, Dealership $dealership): RedirectResponse
    {
        $dealership->update($request->safe()->except(['user_ids']));

        if ($request->has('user_ids')) {
            $dealership->users()->sync($request->validated('user_ids', []));
        }

        return back()->with('success', 'Dealership updated successfully.');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dealership;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DealershipStoreController extends Controller
{
    public function store(Request $request, Dealership $dealership): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:50'],
            'current_solution_name' => ['nullable', 'string', 'max:255'],
            'current_solution_use' => ['nullable', 'string', 'max:255'],
        ]);

        $dealership->stores()->create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Store created successfully.');
    }

    public function update(Request $request, Dealership $dealership, Store $store): RedirectResponse
    {
        abort_unless($store->dealership_id === $dealership->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:50'],
            'current_solution_name' => ['nullable', 'string', 'max:255'],
            'current_solution_use' => ['nullable', 'string', 'max:255'],
        ]);

        $store->update($data);

        return back()->with('success', 'Store updated successfully.');
    }

    public function destroy(Dealership $dealership, Store $store): RedirectResponse
    {
        abort_unless($store->dealership_id === $dealership->id, 404);

        $store->delete();

        return back()->with('success', 'Store deleted successfully.');
    }
}

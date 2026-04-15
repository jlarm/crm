<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Dealership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DealershipContactController extends Controller
{
    public function store(Request $request, Dealership $dealership): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'linkedin_link' => ['nullable', 'string', 'max:255'],
            'primary_contact' => ['nullable', 'boolean'],
        ]);

        $dealership->contacts()->create([
            ...$data,
            'primary_contact' => $request->boolean('primary_contact'),
        ]);

        return back()->with('success', 'Contact created successfully.');
    }

    public function update(Request $request, Dealership $dealership, Contact $contact): RedirectResponse
    {
        abort_unless($contact->dealership_id === $dealership->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'linkedin_link' => ['nullable', 'string', 'max:255'],
            'primary_contact' => ['nullable', 'boolean'],
        ]);

        $contact->update([
            ...$data,
            'primary_contact' => $request->boolean('primary_contact'),
        ]);

        return back()->with('success', 'Contact updated successfully.');
    }

    public function destroy(Dealership $dealership, Contact $contact): RedirectResponse
    {
        abort_unless($contact->dealership_id === $dealership->id, 404);

        $contact->delete();

        return back()->with('success', 'Contact deleted successfully.');
    }
}

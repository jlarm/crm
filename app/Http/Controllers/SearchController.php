<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Dealership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = mb_trim((string) $request->string('q'));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $dealerships = Dealership::search($query)
            ->take(5)
            ->get()
            ->map(fn (Dealership $d): array => [
                'type' => 'dealership',
                'id' => $d->id,
                'label' => $d->name,
                'subtitle' => collect([$d->city, $d->state])->filter()->implode(', '),
                'meta' => $d->type,
                'url' => route('dealerships.show', $d),
            ]);

        $contacts = Contact::search($query)
            ->take(5)
            ->get()
            ->load('dealership')
            ->map(fn (Contact $c): array => [
                'type' => 'contact',
                'id' => $c->id,
                'label' => $c->name,
                'subtitle' => $c->dealership?->name,
                'meta' => $c->position,
                'url' => route('dealerships.show', $c->dealership_id),
            ]);

        return response()->json(
            $dealerships->concat($contacts)->values()
        );
    }
}

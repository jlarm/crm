<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dealerships\FindDealershipOnline;
use App\Http\Requests\DealershipLookupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Finds a dealership on the web so the "New dealership" form can be filled in from it.
 */
final class DealershipLookupController extends Controller
{
    public function __invoke(DealershipLookupRequest $request, FindDealershipOnline $findDealershipOnline): JsonResponse
    {
        try {
            return response()->json($findDealershipOnline(
                $request->string('name')->trim()->toString(),
                $request->filled('city') ? $request->string('city')->trim()->toString() : null,
                $request->filled('state') ? $request->string('state')->trim()->toString() : null,
            ));
        } catch (Throwable $e) {
            Log::warning('[DealershipLookup] Web lookup failed.', [
                'name' => $request->string('name')->toString(),
                'exception' => $e,
            ]);

            return response()->json([
                'message' => "Couldn't search for this dealership right now. You can still fill in the details by hand.",
            ], 503);
        }
    }
}

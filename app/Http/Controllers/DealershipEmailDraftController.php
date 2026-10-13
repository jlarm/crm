<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Emails\DraftDealershipEmail;
use App\Http\Requests\DealershipEmailDraftRequest;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Writes a draft email with Claude for the rep to edit before sending.
 */
final class DealershipEmailDraftController extends Controller
{
    public function __invoke(DealershipEmailDraftRequest $request, Dealership $dealership, DraftDealershipEmail $draftEmail): JsonResponse
    {
        /** @var User $rep */
        $rep = $request->user();
        $replyingTo = $request->filled('parent_id') ? DealershipEmail::find($request->integer('parent_id')) : null;
        $contactId = $request->integer('contact_id') ?: $replyingTo?->contact_id;

        try {
            return response()->json($draftEmail(
                rep: $rep,
                dealership: $dealership,
                contact: $contactId ? Contact::find($contactId) : null,
                replyingTo: $replyingTo,
                notes: $request->filled('notes') ? $request->string('notes')->value() : null,
            ));
        } catch (Throwable $e) {
            Log::warning('[EmailDraft] Drafting failed.', ['dealership_id' => $dealership->id, 'exception' => $e]);

            return response()->json([
                'message' => "Couldn't write a draft right now. Try again, or write the email yourself.",
            ], 503);
        }
    }
}

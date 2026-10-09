<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Opportunities\AnalyzeCallNotes;
use App\Http\Requests\AnalyzeCallNotesRequest;
use App\Models\Dealership;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final class OpportunityActivityAnalysisController extends Controller
{
    public function __invoke(
        AnalyzeCallNotesRequest $request,
        Dealership $dealership,
        Opportunity $opportunity,
        AnalyzeCallNotes $analyzeCallNotes,
    ): JsonResponse {
        try {
            return response()->json($analyzeCallNotes($opportunity, $request->string('notes')->toString()));
        } catch (Throwable $e) {
            Log::warning('[OpportunityActivityAnalysis] Call note analysis failed.', [
                'opportunity_id' => $opportunity->id,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'The AI could not analyze these notes right now. You can still log the activity.',
            ], 503);
        }
    }
}

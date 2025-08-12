<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Services\AiChatService;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->post('/chat', function (Request $request) {
    try {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|string',
        ]);

        $aiService = app(AiChatService::class);
        $response = $aiService->generateResponse(
            $request->input('message'),
            $request->input('session_id'),
            auth()->id()
        );

        return response()->json($response);
    } catch (\Exception $e) {
        \Log::error('Chat API Error: ' . $e->getMessage(), [
            'user_id' => auth()->id(),
            'message' => $request->input('message'),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'response' => 'I encountered a technical issue. Please try again.'
        ], 500);
    }
});

Route::middleware(['auth'])->get('/chat/history', function (Request $request) {
    try {
        $sessionId = $request->query('session_id');
        
        if (!$sessionId) {
            return response()->json(['messages' => []]);
        }
        
        $aiService = app(AiChatService::class);
        $history = $aiService->getChatHistoryForFrontend($sessionId, auth()->id());
        
        return response()->json(['messages' => $history]);
        
    } catch (\Exception $e) {
        \Log::error('Chat History Error: ' . $e->getMessage(), [
            'user_id' => auth()->id(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['messages' => []], 200);
    }
});

Route::middleware(['auth'])->get('/chat/download-csv/{type}', function (Request $request, string $type) {
    try {
        $aiService = app(AiChatService::class);
        $csvData = $aiService->generateCsvReport($type, auth()->id());
        
        $filename = "{$type}_report_" . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            
    } catch (\Exception $e) {
        \Log::error('CSV Download Error: ' . $e->getMessage(), [
            'user_id' => auth()->id(),
            'type' => $type,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Failed to generate CSV report'], 500);
    }
});

//Route::get('/mailable', function () {
//   $email = \App\Models\DealerEmail::first();
//
//   return new App\Mail\DealerEmailMail($email);
//});

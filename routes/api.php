<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\AiChatService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/chat', function (Request $request) {
    try {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'response' => 'Please log in to use the chat feature.'
            ], 401);
        }

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

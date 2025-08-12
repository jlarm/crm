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

//Route::get('/mailable', function () {
//   $email = \App\Models\DealerEmail::first();
//
//   return new App\Mail\DealerEmailMail($email);
//});

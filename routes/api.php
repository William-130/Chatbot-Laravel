<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    // Chatbot routes
    Route::post('/chatbot/message', [ChatbotController::class, 'sendMessage']);
    Route::get('/chatbot/status', [ChatbotController::class, 'getStatus']);
    
    // RAG Management routes
    Route::get('/chatbot/rag/config', [ChatbotController::class, 'getRagConfig']);
    Route::post('/chatbot/rag/config', [ChatbotController::class, 'updateRagConfig']);
    Route::post('/chatbot/rag/test', [ChatbotController::class, 'testRagSearch']);
});

// Fallback untuk debugging
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'POST /api/chatbot/message',
            'GET /api/chatbot/status',
            'GET /api/chatbot/rag/config',
            'POST /api/chatbot/rag/config',
            'POST /api/chatbot/rag/test'
        ]
    ], 404);
});

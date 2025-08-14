<?php

use Illuminate\Support\Facades\Route;

// Add middleware for video files and CORS
Route::middleware(['web'])->group(function () {
    
    Route::get('/', function () {
        return redirect('/demo-center');
    });

    Route::get('/chatbot/enhanced', function () {
        return view('chatbot-enhanced');
    });

    Route::get('/integration-demo', function () {
        return response()->file(public_path('integration-template.html'));
    });

    Route::get('/demo-embed', function () {
        return response()->file(public_path('demo-embed.html'));
    });

    // Chatbot embed route untuk widget dengan SEMUA FITUR LENGKAP
    Route::get('/chatbot/embed', [App\Http\Controllers\ChatbotController::class, 'embedView']);

    // Widget integration guide
    Route::get('/widget-guide', function () {
        return response()->file(public_path('widget-integration-guide.html'));
    });

    // Widget demo
    Route::get('/widget-demo', function () {
    return response()->file(public_path('widget-demo.html'));
});

// External site demo
Route::get('/external-demo', function () {
    return response()->file(public_path('external-site-demo.html'));
});

// Demo center
Route::get('/demo-center', function () {
    return response()->file(public_path('demo-center.html'));
});

// Test widget
Route::get('/test-widget', function () {
    return response()->file(public_path('test-widget.html'));
});

// COMPLETE Widget Test - All Features
Route::get('/complete-test', function () {
    return response()->file(public_path('complete-widget-test.html'));
});

});

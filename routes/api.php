<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\TelegramBotService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Telegram Webhook Endpoint
 * 
 * URL: https://yourdomain.com/api/webhook/telegram
 */
Route::post('/webhook/telegram', function (Request $request) {
    try {
        $update = $request->all();
        
        \Log::info('Telegram webhook received', $update);
        
        $service = new TelegramBotService();
        $service->handleWebhook($update);
        
        return response()->json(['ok' => true]);
        
    } catch (\Exception $e) {
        \Log::error('Telegram webhook error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

/**
 * Set Telegram Webhook (for setup only)
 * 
 * Usage: GET https://yourdomain.com/api/telegram/set-webhook
 */
Route::get('/telegram/set-webhook', function (Request $request) {
    try {
        $webhookUrl = url('/api/webhook/telegram');
        
        $result = TelegramBotService::setWebhook($webhookUrl);
        
        return response()->json([
            'success' => true,
            'webhook_url' => $webhookUrl,
            'result' => $result
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

/**
 * Get Webhook Info
 */
Route::get('/telegram/webhook-info', function () {
    try {
        $bot = new \TelegramBot\Api\BotApi(config('services.telegram.bot_token'));
        $info = $bot->getWebhookInfo();
        
        return response()->json([
            'success' => true,
            'info' => $info
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

/**
 * Test OpenAI Connection
 */
Route::get('/test/openai', function () {
    try {
        $service = new \App\Services\OpenAIService();
        $response = $service->getCEODirective(
            'Test Location',
            'Test User',
            'Đây là test message'
        );
        
        return response()->json([
            'success' => true,
            'response' => $response
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

/**
 * Health Check
 */
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => config('app.name'),
        'version' => '3.0.0'
    ]);
});

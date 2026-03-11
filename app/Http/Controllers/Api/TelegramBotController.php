<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function handle(Request $request)
    {
        $this->telegramService->handleWebhook();
        return response()->json(['status' => 'success']);
    }

    public function setWebhook()
    {
        $url = env('TELEGRAM_WEBHOOK_URL');
        $response = \Telegram\Bot\Laravel\Facades\Telegram::setWebhook(['url' => $url]);

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook set to: ' . $url,
            'response' => $response
        ]);
    }
}

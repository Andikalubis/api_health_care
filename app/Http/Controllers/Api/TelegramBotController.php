<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBotController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming webhooks from Telegram.
     * Menangani webhook yang masuk dari Telegram.
     */
    public function handle(Request $request)
    {
        try {
            $this->telegramService->handleWebhook();
            return $this->successResponse(null, 'Webhook berhasil ditangani.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menangani webhook.', 500);
        }
    }

    /**
     * Set the Telegram webhook URL.
     * Mengatur URL webhook Telegram.
     */
    public function setWebhook()
    {
        try {
            $url = env('TELEGRAM_WEBHOOK_URL');
            $response = Telegram::setWebhook(['url' => $url]);

            return $this->successResponse($response, 'Webhook berhasil diatur ke: ' . $url);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengatur webhook.', 500);
        }
    }
}

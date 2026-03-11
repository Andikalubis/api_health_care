<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\TelegramUser;
use App\Models\PatientData;
use App\Models\HealthCheck;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Send a simple text message.
     */
    public function sendMessage($chatId, $text)
    {
        try {
            return Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown'
            ]);
        } catch (\Exception $e) {
            Log::error("Telegram Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle incoming webhook updates.
     */
    public function handleWebhook()
    {
        $update = Telegram::getWebhookUpdate();
        $message = $update->getMessage();

        if (!$message)
            return;

        $chatId = $message->getChat()->getId();
        $text = $message->getText();
        $username = $message->getChat()->getUsername();

        if (str_starts_with($text, '/start')) {
            $this->handleStartCommand($chatId, $username);
        } elseif (str_starts_with($text, '/status')) {
            $this->handleStatusCommand($chatId);
        } else {
            $this->sendMessage($chatId, "Halo! Gunakan command berikut:\n/start - Hubungkan akun\n/status - Cek kondisi kesehatan terakhir");
        }
    }

    /**
     * Handle /start command.
     */
    protected function handleStartCommand($chatId, $username)
    {
        // Simple logic to link user: in a real app, you might use a deep link token /start {token}
        // For now, we'll just acknowledge the registration if the user already exists in telegram_users

        $telegramUser = TelegramUser::where('telegram_chat_id', $chatId)->first();

        if ($telegramUser) {
            $this->sendMessage($chatId, "Selamat datang kembali! Akun Anda sudah terhubung.");
        } else {
            $this->sendMessage($chatId, "Halo! Silakan gunakan fitur Hubungkan Telegram di aplikasi Health Care untuk mulai menerima notifikasi.");
        }
    }

    /**
     * Handle /status command.
     */
    protected function handleStatusCommand($chatId)
    {
        $telegramUser = TelegramUser::where('telegram_chat_id', $chatId)->first();

        if (!$telegramUser) {
            $this->sendMessage($chatId, "Maaf, akun Anda belum terhubung. Silakan hubungkan melalui aplikasi.");
            return;
        }

        $patient = PatientData::where('user_id', $telegramUser->user_id)->first();

        if (!$patient) {
            $this->sendMessage($chatId, "Data pasien tidak ditemukan.");
            return;
        }

        $latestChecks = HealthCheck::where('patient_id', $patient->id)
            ->with('healthType')
            ->orderBy('check_time', 'desc')
            ->take(3)
            ->get();

        if ($latestChecks->isEmpty()) {
            $this->sendMessage($chatId, "Belum ada data pemeriksaan kesehatan.");
            return;
        }

        $msg = "*Kondisi Kesehatan Terakhir:*\n\n";
        foreach ($latestChecks as $check) {
            $statusEmoji = $check->status == 'normal' ? '✅' : ($check->status == 'warning' ? '⚠️' : '🚨');
            $msg .= "{$statusEmoji} *{$check->healthType->name}*\n";
            $msg .= "Hasil: {$check->result_value} {$check->healthType->unit}\n";
            $msg .= "Status: {$check->status}\n";
            $msg .= "Waktu: " . date('d M Y H:i', strtotime($check->check_time)) . "\n\n";
        }

        $this->sendMessage($chatId, $msg);
    }

    /**
     * Send health alert notification.
     */
    public function sendHealthAlert($userId, $type, $value, $unit, $status, $message)
    {
        $telegramUser = TelegramUser::where('user_id', $userId)->first();
        if (!$telegramUser)
            return;

        $statusEmoji = $status == 'warning' ? '⚠️' : '🚨';
        $alertMsg = "*{$statusEmoji} PERINGATAN KESEHATAN {$statusEmoji}*\n\n";
        $alertMsg .= "Pemeriksaan: *{$type}*\n";
        $alertMsg .= "Hasil: *{$value} {$unit}*\n";
        $alertMsg .= "Status: *{$status}*\n";
        $alertMsg .= "Pesan: {$message}";

        $this->sendMessage($telegramUser->telegram_chat_id, $alertMsg);
    }
}

<?php

namespace App\Services;

use App\Models\HealthCheck;
use App\Models\HealthAlert;
use App\Models\HealthLimit;
use App\Services\TelegramService;

class HealthCheckService extends BaseService
{
    protected $telegramService;

    public function __construct(HealthCheck $model, TelegramService $telegramService)
    {
        parent::__construct($model);
        $this->telegramService = $telegramService;
    }

    /**
     * Override create to check for health alerts.
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        $healthCheck = parent::create($data);

        // Trigger alert check
        $this->checkHealthAlert($healthCheck);

        return $healthCheck;
    }

    /**
     * Check if health check value exceeds limits and trigger alert.
     */
    protected function checkHealthAlert($healthCheck)
    {
        $limit = HealthLimit::where('health_type_id', $healthCheck->health_type_id)->first();

        if ($limit) {
            $level = null;
            if ($healthCheck->value >= $limit->danger_max || $healthCheck->value <= $limit->danger_min) {
                $level = 'danger';
            } elseif ($healthCheck->value >= $limit->warning_max || $healthCheck->value <= $limit->warning_min) {
                $level = 'warning';
            }

            if ($level) {
                $message = "Alert [{$level}]: Patient {$healthCheck->patient->full_name} check for {$healthCheck->healthType->name} is {$healthCheck->value} {$healthCheck->healthType->unit}.";

                HealthAlert::create([
                    'patient_id' => $healthCheck->patient_id,
                    'health_check_id' => $healthCheck->id,
                    'alert_level' => $level,
                    'message' => $message,
                ]);

                // Send Telegram
                $this->telegramService->sendMessage($healthCheck->patient->user->telegramUser->telegram_chat_id ?? null, $message);
            }
        }
    }
}

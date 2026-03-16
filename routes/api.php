<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthTypeController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\MealTypeController;
use App\Http\Controllers\Api\PatientDataController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\VitalSignController;
use App\Http\Controllers\Api\MedicineScheduleController;
use App\Http\Controllers\Api\MedicineHistoryController;
use App\Http\Controllers\Api\MealScheduleController;
use App\Http\Controllers\Api\HealthLimitController;
use App\Http\Controllers\Api\HealthAlertController;
use App\Http\Controllers\Api\TelegramUserController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('health-types', HealthTypeController::class);
    Route::apiResource('medicines', MedicineController::class);
    Route::apiResource('meal-types', MealTypeController::class);
    Route::apiResource('patient-data', PatientDataController::class);
    Route::apiResource('health-checks', HealthCheckController::class);
    Route::apiResource('vital-signs', VitalSignController::class);
    Route::apiResource('medicine-schedules', MedicineScheduleController::class);
    Route::apiResource('medicine-histories', MedicineHistoryController::class);
    Route::apiResource('meal-schedules', MealScheduleController::class);
    Route::apiResource('health-limits', HealthLimitController::class);
    Route::apiResource('health-alerts', HealthAlertController::class);
    Route::apiResource('telegram-users', TelegramUserController::class);
    Route::apiResource('notifications', NotificationController::class);

    // Monitoring Routes
    Route::get('/admin/monitor', [\App\Http\Controllers\Api\MonitoringController::class, 'adminMonitor'])->middleware('role:admin');
    Route::get('/user/monitor', [\App\Http\Controllers\Api\MonitoringController::class, 'userMonitor'])->middleware('role:user');

    // Master Notifications (Admin Only)
    Route::apiResource('master-notifications', \App\Http\Controllers\Api\MasterNotificationController::class)->middleware('role:admin');

    // Combined Medical Record Routes
    Route::post('/medical-records', [\App\Http\Controllers\Api\MedicalRecordController::class, 'store']);
    Route::get('/medical-records/{patient_id}', [\App\Http\Controllers\Api\MedicalRecordController::class, 'show']);

    Route::post('/telegram/set-webhook', [\App\Http\Controllers\Api\TelegramBotController::class, 'setWebhook']);
});

Route::post('/telegram/webhook', [\App\Http\Controllers\Api\TelegramBotController::class, 'handle']);

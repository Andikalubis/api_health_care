<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate all tables to ensure a clean state
        DB::table('users')->truncate();
        DB::table('patient_data')->truncate();
        DB::table('health_types')->truncate();
        DB::table('health_checks')->truncate();
        DB::table('vital_signs')->truncate();
        DB::table('medicines')->truncate();
        DB::table('medicine_schedules')->truncate();
        DB::table('medicine_histories')->truncate();
        DB::table('meal_types')->truncate();
        DB::table('meal_schedules')->truncate();
        DB::table('health_limits')->truncate();
        DB::table('health_alerts')->truncate();
        DB::table('telegram_users')->truncate();
        DB::table('notifications')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =========================
        // USER ADMIN
        // =========================
        $userId = DB::table('users')->insertGetId([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // =========================
        // PATIENT DATA
        // =========================
        $patientId = DB::table('patient_data')->insertGetId([
            'id' => 1,
            'user_id' => $userId,
            'name' => 'Andika Lubis',
            'gender' => 'male',
            'birth_date' => '1999-05-12',
            'height' => 170,
            'weight' => 70,
            'blood_type' => 'O',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // =========================
        // HEALTH TYPES
        // =========================
        DB::table('health_types')->insert([
            ['id' => 1, 'name' => 'Diabetes', 'unit' => 'mg/dL', 'normal_min' => 70, 'normal_max' => 140, 'description' => 'Gula darah'],
            ['id' => 2, 'name' => 'Asam Urat', 'unit' => 'mg/dL', 'normal_min' => 3.5, 'normal_max' => 7.0, 'description' => 'Asam urat dalam darah'],
            ['id' => 3, 'name' => 'Kolesterol', 'unit' => 'mg/dL', 'normal_min' => 0, 'normal_max' => 200, 'description' => 'Kolesterol total'],
        ]);

        // =========================
        // HEALTH CHECKS
        // =========================
        DB::table('health_checks')->insert([
            [
                'id' => 1,
                'patient_id' => $patientId,
                'health_type_id' => 1,
                'result_value' => 180,
                'status' => 'warning',
                'notes' => 'Gula darah agak tinggi',
                'check_time' => '2026-03-10 07:30:00',
                'created_at' => Carbon::now()
            ],
            [
                'id' => 2,
                'patient_id' => $patientId,
                'health_type_id' => 2,
                'result_value' => 6.5,
                'status' => 'normal',
                'notes' => 'Masih normal',
                'check_time' => '2026-03-10 08:00:00',
                'created_at' => Carbon::now()
            ],
            [
                'id' => 3,
                'patient_id' => $patientId,
                'health_type_id' => 3,
                'result_value' => 220,
                'status' => 'danger',
                'notes' => 'Kolesterol tinggi',
                'check_time' => '2026-03-10 09:00:00',
                'created_at' => Carbon::now()
            ]
        ]);

        // =========================
        // VITAL SIGNS
        // =========================
        DB::table('vital_signs')->insert([
            [
                'id' => 1,
                'patient_id' => $patientId,
                'blood_pressure' => '120/80',
                'heart_rate' => 72,
                'body_temperature' => 36.5,
                'breathing_rate' => 18,
                'oxygen_level' => 98,
                'check_time' => '2026-03-10 07:00:00',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'patient_id' => $patientId,
                'blood_pressure' => '130/85',
                'heart_rate' => 75,
                'body_temperature' => 36.7,
                'breathing_rate' => 20,
                'oxygen_level' => 97,
                'check_time' => '2026-03-11 07:00:00',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        // =========================
        // MEDICINES
        // =========================
        DB::table('medicines')->insert([
            ['id' => 1, 'name' => 'Metformin', 'description' => 'Obat diabetes'],
            ['id' => 2, 'name' => 'Allopurinol', 'description' => 'Obat asam urat'],
            ['id' => 3, 'name' => 'Simvastatin', 'description' => 'Obat kolesterol'],
        ]);

        // =========================
        // MEDICINE SCHEDULE
        // =========================
        DB::table('medicine_schedules')->insert([
            [
                'id' => 1,
                'patient_id' => $patientId,
                'medicine_id' => 1,
                'dosage' => '500 mg',
                'drink_time' => '07:00',
                'start_date' => '2026-03-01',
                'end_date' => '2026-04-01',
                'notes' => 'Minum setelah makan',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'patient_id' => $patientId,
                'medicine_id' => 3,
                'dosage' => '10 mg',
                'drink_time' => '19:00',
                'start_date' => '2026-03-01',
                'end_date' => '2026-04-01',
                'notes' => 'Minum sebelum tidur',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        // =========================
        // MEDICINE HISTORY
        // =========================
        DB::table('medicine_histories')->insert([
            ['id' => 1, 'schedule_id' => 1, 'taken_time' => '2026-03-10 07:00:00', 'status' => 'taken', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'schedule_id' => 2, 'taken_time' => '2026-03-10 19:00:00', 'status' => 'taken', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // =========================
        // MEAL TYPES
        // =========================
        DB::table('meal_types')->insert([
            ['id' => 1, 'name' => 'Sarapan'],
            ['id' => 2, 'name' => 'Makan Siang'],
            ['id' => 3, 'name' => 'Makan Malam'],
            ['id' => 4, 'name' => 'Snack'],
        ]);

        // =========================
        // MEAL SCHEDULE
        // =========================
        DB::table('meal_schedules')->insert([
            [
                'id' => 1,
                'patient_id' => $patientId,
                'meal_type_id' => 1,
                'meal_time' => '07:00',
                'notes' => 'Sarapan pagi',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'patient_id' => $patientId,
                'meal_type_id' => 2,
                'meal_time' => '12:00',
                'notes' => 'Makan siang',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'patient_id' => $patientId,
                'meal_type_id' => 3,
                'meal_time' => '18:30',
                'notes' => 'Makan malam',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        // =========================
        // HEALTH LIMITS
        // =========================
        DB::table('health_limits')->insert([
            ['id' => 1, 'health_type_id' => 1, 'warning_min' => 141, 'warning_max' => 199, 'danger_min' => 200, 'danger_max' => 500, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 2, 'health_type_id' => 2, 'warning_min' => 7.1, 'warning_max' => 8.0, 'danger_min' => 8.1, 'danger_max' => 20, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['id' => 3, 'health_type_id' => 3, 'warning_min' => 201, 'warning_max' => 239, 'danger_min' => 240, 'danger_max' => 500, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // =========================
        // HEALTH ALERTS
        // =========================
        DB::table('health_alerts')->insert([
            ['id' => 1, 'health_check_id' => 3, 'alert_level' => 'danger', 'message' => 'Kolesterol melebihi batas normal', 'sent_status' => false, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        // =========================
        // TELEGRAM USERS
        // =========================
        DB::table('telegram_users')->insert([
            [
                'id' => 1,
                'user_id' => $userId,
                'telegram_chat_id' => '123456789',
                'telegram_username' => 'health_care_bot',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        // =========================
        // NOTIFICATIONS
        // =========================
        DB::table('notifications')->insert([
            [
                'id' => 1,
                'user_id' => $userId,
                'title' => 'Peringatan Kolesterol',
                'message' => 'Kolesterol Anda tinggi',
                'notification_type' => 'health_alert',
                'send_time' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
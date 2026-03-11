<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_data')->onDelete('cascade');
            $table->string('blood_pressure', 20);
            $table->integer('heart_rate');
            $table->decimal('body_temperature', 4, 1);
            $table->integer('breathing_rate');
            $table->integer('oxygen_level');
            $table->dateTime('check_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};

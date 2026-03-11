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
        Schema::create('meal_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_data')->onDelete('cascade');
            $table->foreignId('meal_type_id')->constrained('meal_types')->onDelete('cascade');
            $table->time('meal_time');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_schedules');
    }
};

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
        Schema::create('medicine_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('medicine_schedules')->onDelete('cascade');
            $table->dateTime('taken_time');
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_histories');
    }
};

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
        Schema::create('health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patient_data')->onDelete('cascade');
            $table->foreignId('health_type_id')->constrained('health_types')->onDelete('cascade');
            $table->decimal('result_value', 8, 2);
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->dateTime('check_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_checks');
    }
};

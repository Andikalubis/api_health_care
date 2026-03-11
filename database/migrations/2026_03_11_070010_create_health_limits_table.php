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
        Schema::create('health_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_type_id')->constrained('health_types')->onDelete('cascade');
            $table->decimal('warning_min', 8, 2);
            $table->decimal('warning_max', 8, 2);
            $table->decimal('danger_min', 8, 2);
            $table->decimal('danger_max', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_limits');
    }
};

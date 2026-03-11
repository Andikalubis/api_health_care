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
        Schema::create('health_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_check_id')->constrained('health_checks')->onDelete('cascade');
            $table->string('alert_level', 20);
            $table->text('message');
            $table->boolean('sent_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_alerts');
    }
};

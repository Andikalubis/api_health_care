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
        Schema::create('health_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('unit', 20);
            $table->decimal('normal_min', 8, 2);
            $table->decimal('normal_max', 8, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_types');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('palette', 30)->default('sakura');
            $table->string('appearance', 10)->default('os');
            $table->string('timezone', 64)->default('UTC');
            $table->string('week_start', 10)->default('sunday');
            $table->string('time_format', 2)->default('12');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_preferences');
    }
};

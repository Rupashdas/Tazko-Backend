<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('label', 100);
            $table->timestamps();
            $table->unique(['workspace_id', 'name']);
        });

        // Capability names live in code (App\Support\CapabilityRegistry), so
        // there is no capabilities table to keep in step with it.
        Schema::create('role_capabilities', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('capability', 100);
            $table->primary(['role_id', 'capability']);
        });

        Schema::table('workspace_members', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('workspace_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('role_capabilities');
        Schema::dropIfExists('roles');
    }
};

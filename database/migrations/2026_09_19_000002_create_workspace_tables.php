<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 60)->unique();
            // restrict: an owner's account cannot be deleted out from under
            // a workspace. Ownership has to be handed on first.
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

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

        Schema::create('workspace_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['workspace_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('workspace_members');
        Schema::dropIfExists('role_capabilities');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('workspaces');
    }
};

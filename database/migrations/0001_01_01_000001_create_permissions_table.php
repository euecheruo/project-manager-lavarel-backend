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
        // 1. ROLES TABLE (Lookup)
        // We create this first so we can reference it immediately if needed.
        Schema::create('roles', function (Blueprint $table) {
            // Using integer() + autoIncrement() to match PostgreSQL 'SERIAL' (4 bytes)
            // If you wanted 'BIGSERIAL' (8 bytes), you would use $table->id('role_id');
            $table->integer('role_id')->autoIncrement();

            // "Executive", "Manager", "Associate"
            $table->string('role_name', 50)->unique();
        });

        // 2. PERMISSIONS TABLE (Lookup)
        // Granular actions like "projects.create" or "reviews.delete_own"
        Schema::create('permissions', function (Blueprint $table) {
            $table->integer('permission_id')->autoIncrement();
            $table->string('permission_name', 50)->unique();
        });

        // 3. ROLE_PERMISSIONS TABLE (Pivot)
        // The Authorization Matrix: Connects Roles to Permissions.
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('permission_id');

            // Foreign Key Constraints
            $table->foreign('role_id')
                ->references('role_id')
                ->on('roles')
                ->onDelete('cascade');

            $table->foreign('permission_id')
                ->references('permission_id')
                ->on('permissions')
                ->onDelete('cascade');

            // Composite Primary Key to prevent duplicate assignments
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. USERS TABLE
        // The core entity. We include it here because it is the standard 
        // 000000 migration responsibility in Laravel.
        Schema::create('users', function (Blueprint $table) {
            $table->integer('user_id')->autoIncrement();

            $table->string('first_name', 100);
            $table->string('last_name', 100);

            $table->string('email', 150)->unique();

            // We use char(60) because Bcrypt hashes are always 60 characters
            $table->char('password_hash', 60);

            $table->boolean('is_active')->default(true);

            // Standard Laravel timestamps (created_at, updated_at)
            $table->timestamps();

            // Soft Deletes (deleted_at) - critical for audit trails
            $table->softDeletes();
        });

        // 5. SESSIONS TABLE (Optional but Recommended)
        // Even though we use JWT, Laravel sometimes needs this table 
        // if you use any standard web guard features or database session drivers.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order of dependency
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};

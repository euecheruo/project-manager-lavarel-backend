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
        Schema::create('users', function (Blueprint $table) {
            // 1. Primary Key: Maps to PostgreSQL 'SERIAL'
            // We use 'user_id' explicitly instead of 'id'
            $table->integer('user_id')->autoIncrement();

            // 2. Identity Fields
            $table->string('first_name', 100);
            $table->string('last_name', 100);

            // 3. Authentication
            // 'unique()' automatically creates an index. 
            // We specify the name 'idx_users_email' to match your SQL schema exactly.
            $table->string('email', 150)->unique('idx_users_email');

            // Fixed length char(60) is optimized for Bcrypt/Argon2 hashes
            $table->char('password_hash', 60);

            // 4. Status flags
            $table->boolean('is_active')->default(true);

            // 5. Audit Timestamps
            // Creates 'created_at' and 'updated_at' columns
            $table->timestamps();

            // 6. Soft Deletes
            // Creates 'deleted_at' column (nullable timestamp)
            // Allows you to "deactivate" users without losing their history
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

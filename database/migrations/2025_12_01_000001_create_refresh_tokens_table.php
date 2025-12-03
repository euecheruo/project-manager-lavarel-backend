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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            // 1. Primary Key: Maps to PostgreSQL 'SERIAL'
            $table->integer('token_id')->autoIncrement();

            // 2. Foreign Key: Links to the User who owns this token
            // We define the column first, then the constraint below.
            $table->integer('user_id');

            // 3. Security: Token Hash
            // We store the SHA-256 hash of the token, not the raw token.
            // SHA-256 output is always 64 hexadecimal characters.
            $table->char('token_hash', 64)->unique();

            // 4. Revocation Status
            // Allows us to kill a specific session (e.g., "Log out of all devices")
            $table->boolean('is_revoked')->default(false);

            // 5. Expiration
            // Crucial for the cleanup worker to know when to delete old rows.
            $table->timestamp('expires_at');

            // 6. Audit Timestamp
            // We only need created_at for tokens; they are rarely "updated", just revoked.
            $table->timestamp('created_at')->useCurrent();

            // 7. Foreign Key Constraint
            // If a User is deleted, their refresh tokens should be wiped instantly.
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};

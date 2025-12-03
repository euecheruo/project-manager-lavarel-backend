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
        Schema::create('teams', function (Blueprint $table) {
            // 1. Primary Key: Maps to PostgreSQL 'SERIAL'
            // Explicitly named 'team_id'
            $table->integer('team_id')->autoIncrement();

            // 2. Team Name
            // e.g., "Frontend Alpha", "Backend Delta", "QA Team"
            $table->string('name', 100);

            // 3. Timestamps
            // Tracks created_at and updated_at
            $table->timestamps();

            // Note: We do NOT put 'manager_id' here.
            // Why? Because a team might change managers, or have no manager temporarily.
            // We define the Manager via the 'user_roles' and 'team_members' intersection,
            // or we could add a specific column later if strict 1-manager-per-team is required.
            // Based on your spec ("Each team is made up a manager and multiple associates"), 
            // the structure is defined by the Roles + Team Membership intersection.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};

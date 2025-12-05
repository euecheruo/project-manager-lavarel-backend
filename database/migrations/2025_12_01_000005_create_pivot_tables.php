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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('role_id');
            $table->timestamp('assigned_at')->useCurrent();

            $table->primary(['user_id', 'role_id']);

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('restrict');
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('team_id');
            $table->timestamp('joined_at')->useCurrent();

            $table->primary(['user_id', 'team_id']);

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('team_id')->references('team_id')->on('teams')->onDelete('cascade');
        });

        Schema::create('project_teams', function (Blueprint $table) {
            $table->integer('project_id');
            $table->integer('team_id');
            $table->timestamp('assigned_at')->useCurrent();

            $table->primary(['project_id', 'team_id']);

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('team_id')->references('team_id')->on('teams')->onDelete('cascade');
        });

        Schema::create('project_advisors', function (Blueprint $table) {
            $table->integer('project_id');
            $table->integer('user_id');
            $table->timestamp('assigned_at')->useCurrent();

            $table->primary(['project_id', 'user_id']);

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_advisors');
        Schema::dropIfExists('project_teams');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('user_roles');
    }
};

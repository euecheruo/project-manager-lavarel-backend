<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->integer('review_id')->autoIncrement();

            $table->integer('project_id');
            $table->integer('reviewer_id');

            $table->text('content'); 
            $table->integer('rating');

            $table->timestamps();

            $table->foreign('project_id')
                ->references('project_id')
                ->on('projects')
                ->onDelete('cascade');

            $table->foreign('reviewer_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('project_id', 'idx_reviews_project');
            $table->index('reviewer_id', 'idx_reviews_reviewer');
        });

        DB::statement('ALTER TABLE reviews ADD CONSTRAINT check_rating_range CHECK (rating >= 1 AND rating <= 5)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

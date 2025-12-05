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
        Schema::create('projects', function (Blueprint $table) {
            $table->integer('project_id')->autoIncrement();

            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->string('status', 50)->default('active');

            $table->integer('created_by')->nullable();

            $table->timestamps(); 
            $table->softDeletes();

            $table->foreign('created_by')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

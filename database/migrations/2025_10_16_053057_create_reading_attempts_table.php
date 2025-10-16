<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reading_attempts', function (Blueprint $table) {
            $table->id();
             // Foreign keys
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reading_exercise_id')->constrained()->onDelete('cascade');

            // Custom columns
            $table->integer('attempts')->default(0);
            $table->enum('status', ['success', 'failed'])->default('failed');
            $table->string('type')->nullable()->default('reading');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_attempts');
    }
};

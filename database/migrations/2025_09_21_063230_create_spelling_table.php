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
        Schema::create('spelling_table', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id'); // Auth user
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('book-outline');
            $table->integer('attempts')->default(3);
            $table->json('letters_to_remove'); // store array of {word, letter}
            $table->integer('score')->default(10);
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spelling_table');
    }
};

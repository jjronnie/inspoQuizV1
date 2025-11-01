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
         Schema::create('questions', function (Blueprint $table) {
            $table->id();
            // Link back to the quiz
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            
            $table->text('text'); // The actual question text
            $table->unsignedSmallInteger('points')->default(1); // Marks for the question

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

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
         Schema::create('answers', function (Blueprint $table) {
            $table->id();
            // Link back to the question
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            
            $table->string('text'); // The answer option text
            $table->boolean('is_correct')->default(false); // Indicates the correct answer for the question

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};

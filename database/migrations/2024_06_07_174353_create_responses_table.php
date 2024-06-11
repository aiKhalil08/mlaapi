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
        Schema::create('quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_id')->unsigned();
            $table->bigInteger('question_id')->unsigned();
            $table->bigInteger('option_id')->unsigned();

            $table->foreign('session_id')->references('id')->on('assignment_user')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('quiz_questions')->onDelete('cascade');
            $table->foreign('option_id')->references('id')->on('question_options')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_responses');
    }
};

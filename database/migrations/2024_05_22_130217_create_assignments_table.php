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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('quiz_id')->unsigned();
            // $table->enum('shuffle', ['none', 'all', 'questions', 'options'])->(['foo', 'bar']);
            // $table->boolean('user_notified')->default(false);
            $table->tinyInteger('status_id')->unsigned();
            $table->timestamp('assigned_at')->useCurrent();
            $table->bigInteger('assigned_by')->unsigned()->nullable();
            $table->timestamp('date_started')->nullable();
            $table->timestamp('date_completed')->nullable();
            $table->integer('score')->unsigned()->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('assignment_statuses')->onDelete('restrict');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

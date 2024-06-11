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
            $table->bigInteger('quiz_id')->unsigned();
            $table->string('name', 100)->unique();
            $table->text('description');
            $table->integer('duration');
            $table->json('shuffle');
            $table->longText('quiz_snapshot');
            $table->tinyInteger('status_id')->unsigned();
            $table->timestamp('created_at')->useCurrent();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('assignment_statuses')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
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

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
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('course_type');
            $table->bigInt('course_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('start_date')->nullable();
            $table->json('duration')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->json('duration')->nullable();
            $table->tinyInteger('status_id');


            $table->foreign('status_id')->references('id')->on('cohort_statuses')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};

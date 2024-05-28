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
        Schema::create('certficates', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type_id');
            $table->bigInt('cohort_id');
            $table->string('course_type');
            $table->bigInt('course_type');
            $table->bigInt('student_id');
            $table->string('url');

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('certificates_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certficates');
    }
};

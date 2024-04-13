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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->bigInt('student_id');
            $table->bigInt('sale_type_id');
            $table->bigInt('cohort_id')->nullable();
            $table->bigInt('course_id')->nullable();
            $table->string('course_type')->nullable();
            $table->string('price');
            $table->timestamp('date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

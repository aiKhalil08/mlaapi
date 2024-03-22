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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->bigInt('course_id')->unsigned();
            $table->string('course_type');
            $table->bigInt('student_id')->unsigned();
            // $table->string('owner_type');
            $table->timestamp('added_at')->nullable()->useCurrent();
            $table->unique(['item_id', 'item_type', 'student_id'], 'item_owner_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

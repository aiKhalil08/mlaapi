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
        Schema::create('testimonials_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable();
            $table->string('name', 255);
            $table->string('company', 255)->nullable();
            $table->string('designation', 255)->nullable();
            $table->text('message')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->timestamp('date_added')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonial_histories');
    }
};

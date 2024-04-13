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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('company', 255)->nullable();
            $table->string('designation', 255)->nullable();
            $table->text('message')->nullable();
            $table->string('image_url', 255)->nullable();
            // $table->string('author', 100)->nullable()->default('Admin');
            // $table->dateTime('created_at')->nullable()->useCurrent();
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};

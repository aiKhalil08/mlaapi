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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('heading', 255);
            $table->text('content')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->string('author', 100)->nullable()->default('Admin');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->unique('heading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};

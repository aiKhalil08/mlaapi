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
        Schema::create('offshore_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('overview');
            $table->json('objectives')->nullable();
            $table->json('attendees')->nullable();
            $table->json('prerequisites')->nullable();
            $table->json('modules')->nullable();
            $table->json('date')->nullable();
            $table->json('price')->nullable();
            $table->enum('location', ['Ghana', 'Kenya'])->nullable();
            $table->tinyInteger('discount')->unsigned()->nullable();
            $table->string('image_url', 255)->nullable();
            $table->unique('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offshore_courses');
    }
};

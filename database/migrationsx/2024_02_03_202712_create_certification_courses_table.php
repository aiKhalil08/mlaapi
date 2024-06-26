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
        Schema::create('certification_courses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 15);
            $table->string('title', 255);
            $table->text('overview');
            $table->json('objectives')->nullable();
            $table->json('attendees')->nullable();
            $table->json('prerequisites')->nullable();
            $table->json('modules')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->unique('code');
            $table->unique('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_courses');
    }
};

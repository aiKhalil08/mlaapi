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
        Schema::create('events', function (Blueprint $table) {
            // $table->id();
            // $table->timestamps();
            $table->id();
            $table->string('name', 255);
            $table->text('description');
            // $table->json('eligible');
            $table->json('date');
            $table->enum('type', ['physical', 'virtual']);
            $table->json('price');
            $table->json('attendees');
            $table->string('image_url', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

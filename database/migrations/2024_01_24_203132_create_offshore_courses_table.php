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
            // $table->string('code', 15);
            // $table->text('description');
            // $table->json('eligible');
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('duration')->unsigned();
            $table->string('location', 100);
            $table->decimal('cost', 13, 2);
            $table->tinyInteger('discount')->unsigned();
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

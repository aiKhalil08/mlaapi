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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            // $table->timestamps();
            $table->string('token', 100);
            $table->enum('tokenable_type', ['admin', 'student', 'tutor']);
            $table->bigInt('tokenable_id');
            $table->timestamp('created_at');
            $table->timestamp('last_used')->nullable();
            $table->timestamp('expires_at');
            $table->unique('token_unique');
            $table->unique(['tokenable_type', 'tokenable_id'], 'type_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};

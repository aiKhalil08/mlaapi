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
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->bigInt('student_id');
            $table->integer('commission')->unsigned()->default(5);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_codes');
    }
};

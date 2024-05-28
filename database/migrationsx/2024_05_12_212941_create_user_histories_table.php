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
        Schema::create('users_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable();
            $table->string('first_name', 30);
            $table->string('last_name', 50);
            $table->string('email', 50);
            $table->string('phone_number', 15);
            $table->string('password');
            $table->string('image_url');
            $table->boolean('email_verified')->default(0);
            $table->string('home_address');
            $table->string('bio');
            $table->bigInteger('user_id')->nullable();
            $table->timestamp('date_added')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_histories');
    }
};

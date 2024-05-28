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
        Schema::create('permissions_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable();
            $table->json('permissions');
            $table->bigInteger('user_id')->nullable();
            $table->timestamp('date_added')->useCurrent();

            $table->foreign('user_id', 'permissionsHistory_users_fk')->references('id')->on('users')->onDelete('set null'); // references the actor (admin)
            $table->foreign('parent_id', 'permissionsHistory_users_fk')->references('id')->on('users')->onDelete('set null'); // references the object
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions_histories');
    }
};

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
        Schema::create('roles_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->json('roles');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->timestamp('date_added')->useCurrent();

            $table->foreign('user_id', 'rolesHistory_users_fk')->references('id')->on('users')->onDelete('set null'); // references the actor (admin)
            $table->foreign('parent_id', 'rolesHistory_users_fk')->references('id')->on('users')->onDelete('set null'); // references the object
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_histories');
    }
};

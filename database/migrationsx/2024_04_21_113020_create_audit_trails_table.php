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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->enum('action', ['create', 'updated', 'deleted', 'updated-roles']);
            $table->bigInt('user_id');
            $table->json('subject');
            $table->timestamp('date_added');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};

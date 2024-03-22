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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6);
            $table->string('owner_type');
            $table->bigInt('owner_id');
            $table->timestamp('created_at');
            $table->timestamp('expires_at');
            $table->unique(['owner_type', 'owner_id'], 'type_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('o_t_p_s');
    }
};

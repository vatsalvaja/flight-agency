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
        Schema::table('assign_luggages', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable();
            $table->json('delivery_proof_images')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assign_luggages', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'delivery_proof_images']);
        });
    }
};

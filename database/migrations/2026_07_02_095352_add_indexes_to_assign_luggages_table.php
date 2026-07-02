<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assign_luggages', function (Blueprint $table) {
            $table->index('status');
            $table->index('driver_id');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assign_luggages', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['driver_id']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['created_at']);
        });
    }
};

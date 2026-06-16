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
        Schema::table('settings', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('settings', 'admin_email')) {
                $columnsToDrop[] = 'admin_email';
            }
            if (Schema::hasColumn('settings', 'company_phone')) {
                $columnsToDrop[] = 'company_phone';
            }
            if (Schema::hasColumn('settings', 'company_address')) {
                $columnsToDrop[] = 'company_address';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('admin_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('company_address')->nullable();
        });
    }
};

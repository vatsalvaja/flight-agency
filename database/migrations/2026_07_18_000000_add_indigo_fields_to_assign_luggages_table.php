<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the IndiGo delayed-baggage fields that are auto-filled from an uploaded
     * document via OCR. Every column is nullable so the existing create/edit flow
     * (and every other airline) is completely unaffected.
     */
    public function up(): void
    {
        Schema::table('assign_luggages', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('notes');
            $table->unsignedSmallInteger('number_of_bags')->nullable()->after('reference_number');
            $table->date('pickup_date')->nullable()->after('number_of_bags');
            $table->date('delivery_date')->nullable()->after('pickup_date');
            $table->string('pnr_number')->nullable()->after('delivery_date');
            $table->string('customer_name')->nullable()->after('pnr_number');
            $table->string('contact_number')->nullable()->after('customer_name');
            $table->text('customer_address')->nullable()->after('contact_number');
            $table->string('pincode')->nullable()->after('customer_address');
            $table->string('indigo_document_path')->nullable()->after('pincode');

            $table->index('reference_number');
            $table->index('pnr_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assign_luggages', function (Blueprint $table) {
            $table->dropIndex(['reference_number']);
            $table->dropIndex(['pnr_number']);
            $table->dropColumn([
                'reference_number',
                'number_of_bags',
                'pickup_date',
                'delivery_date',
                'pnr_number',
                'customer_name',
                'contact_number',
                'customer_address',
                'pincode',
                'indigo_document_path',
            ]);
        });
    }
};

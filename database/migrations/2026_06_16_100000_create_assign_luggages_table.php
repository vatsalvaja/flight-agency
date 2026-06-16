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
        Schema::create('assign_luggages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->string('pickup_location');
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            $table->string('drop_location');
            $table->decimal('drop_latitude', 10, 8)->nullable();
            $table->decimal('drop_longitude', 11, 8)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->date('expected_delivery_date');
            $table->enum('status', ['Pickup', 'In Progress', 'Delivered'])->default('Pickup');
            $table->text('notes')->nullable();
            $table->json('images')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_luggages');
    }
};

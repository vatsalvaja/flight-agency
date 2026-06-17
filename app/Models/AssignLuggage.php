<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'station_id',
    'driver_id',
    'pickup_location',
    'pickup_latitude',
    'pickup_longitude',
    'drop_location',
    'drop_latitude',
    'drop_longitude',
    'distance_km',
    'expected_delivery_date',
    'status',
    'notes',
    'images',
    'created_by',
    'delivered_at',
    'delivery_proof_images',
])]
class AssignLuggage extends Model
{
    use HasFactory;

    protected $table = 'assign_luggages';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_delivery_date' => 'date',
            'images' => 'array',
            'pickup_latitude' => 'decimal:8',
            'pickup_longitude' => 'decimal:8',
            'drop_latitude' => 'decimal:8',
            'drop_longitude' => 'decimal:8',
            'distance_km' => 'decimal:2',
            'delivered_at' => 'datetime',
            'delivery_proof_images' => 'array',
        ];
    }

    /**
     * Get the company associated with the assignment.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Get the station associated with the assignment.
     */
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    /**
     * Get the driver associated with the assignment.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the user who created this assignment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

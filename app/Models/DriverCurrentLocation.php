<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCurrentLocation extends Model
{
    use HasFactory;

    protected $table = 'driver_current_locations';

    protected $primaryKey = 'driver_id';

    public $incrementing = false;

    protected $fillable = [
        'driver_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'battery_level',
        'is_online',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'battery_level' => 'integer',
        'is_online' => 'boolean',
    ];

    /**
     * Get the driver user associated with this location.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}

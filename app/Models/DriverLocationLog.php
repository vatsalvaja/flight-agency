<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLocationLog extends Model
{
    use HasFactory;

    protected $table = 'driver_location_logs';

    public $timestamps = false; // We use created_at manually

    protected $fillable = [
        'order_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the order associated with the location log.
     */
    public function order()
    {
        return $this->belongsTo(AssignLuggage::class, 'order_id');
    }

    /**
     * Get the driver associated with the location log.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}

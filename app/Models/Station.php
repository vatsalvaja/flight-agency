<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'station_name',
    'station_code',
    'city',
    'state',
    'country',
    'address',
    'contact_number',
    'email',
    'status'
])]
class Station extends Model
{
    use HasFactory;
}

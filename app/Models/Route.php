<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'ticketing';

    protected $fillable = [
        'route_name',
        'origin',
        'destination',
        'stops',
        'distance',
        'estimated_duration',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'stops' => 'array',
        'distance' => 'decimal:2',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'estimated_duration' => 'integer',
    ];

    // Relationships
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'ticketing';

    protected $fillable = [
        'route_id',
        'bus_code',
        'departure_time',
        'arrival_time',
        'total_seats',
        'available_seats',
        'price',
        'is_active',
    ];

    protected $casts = [
        'departure_time' => 'datetime:H:i',
        'arrival_time' => 'datetime:H:i',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function scheduleDates()
    {
        return $this->hasMany(ScheduleDate::class);
    }

    /**
     * Check if the schedule operates on a specific date
     */
    public function operatesOnDate($date)
    {
        return $this->scheduleDates()
            ->where('scheduled_date', $date)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get all active dates for this schedule
     */
    public function getActiveDatesAttribute()
    {
        return $this->scheduleDates()
            ->where('is_active', true)
            ->where('scheduled_date', '>=', today())
            ->orderBy('scheduled_date')
            ->pluck('scheduled_date')
            ->toArray();
    }
}

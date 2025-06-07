<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seat extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'ticketing';

    protected $fillable = [
        'schedule_id',
        'seat_number',
        'travel_date',
        'status',
        'booking_id',
    ];

    protected $casts = [
        'travel_date' => 'date',
    ];

    /**
     * Check if the seat is available for booking
     *
     * @return bool
     */
    public function getIsAvailableAttribute()
    {
        return $this->status === 'available';
    }

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

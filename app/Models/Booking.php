<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'ticketing';

    protected $fillable = [
        'booking_code',
        'user_id',
        'schedule_id',
        'travel_date',
        'seat_count',
        'seat_numbers',
        'passenger_details',
        'total_amount',
        'status',
        'booking_date',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'seat_numbers' => 'array',
        'passenger_details' => 'array',
        'total_amount' => 'decimal:2',
        'booking_date' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Backward compatibility: Get the first seat for single bookings
     * This provides compatibility for views that expect $booking->seat
     */
    public function getSeatAttribute()
    {
        // For single seat bookings, return the first seat
        if ($this->seat_count == 1 && !empty($this->seat_numbers)) {
            // Create a mock object with seat_number for backward compatibility
            return (object) [
                'seat_number' => $this->seat_numbers[0]
            ];
        }
        return null;
    }

    /**
     * Get passenger name for single bookings (backward compatibility)
     */
    public function getPassengerNameAttribute()
    {
        if ($this->seat_count == 1 && !empty($this->passenger_details)) {
            return $this->passenger_details[0]['name'] ?? null;
        }
        return null;
    }

    /**
     * Get passenger phone for single bookings (backward compatibility)
     */
    public function getPassengerPhoneAttribute()
    {
        if ($this->seat_count == 1 && !empty($this->passenger_details)) {
            return $this->passenger_details[0]['phone'] ?? null;
        }
        return null;
    }

    /**
     * Get total price (backward compatibility)
     */
    public function getTotalPriceAttribute()
    {
        return $this->total_amount;
    }
}

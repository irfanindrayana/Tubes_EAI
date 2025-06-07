<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'reviews';

    protected $fillable = [
        'user_id',
        'booking_id',
        'route_id',
        'rating',
        'comment',
        'aspects_rating',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'aspects_rating' => 'array',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}

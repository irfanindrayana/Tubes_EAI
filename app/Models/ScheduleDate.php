<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleDate extends Model
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
        'scheduled_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the schedule that owns this date.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}

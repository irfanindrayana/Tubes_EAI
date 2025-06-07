<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'user_management';

    protected $fillable = [
        'user_id',
        'avatar',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

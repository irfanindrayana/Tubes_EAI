<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRecipient extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'inbox';
    
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $fillable = [
        'message_id',
        'recipient_id',
        'read_at',
        'is_starred',
        'is_archived'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'is_starred' => 'boolean',
        'is_archived' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the message that owns the recipient.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the recipient user.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'inbox';

    protected $fillable = [
        'message_code',
        'sender_id',
        'subject',
        'content',
        'type',
        'priority',
        'attachments',
        'sent_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function sender()
    {
        // Note: This relationship crosses database connections
        // Use manual loading in controllers instead
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function messageRecipients()
    {
        return $this->hasMany(MessageRecipient::class, 'message_id');
    }

    // Helper method to get recipients manually
    public function getRecipientsAttribute()
    {
        // If recipients were manually loaded by the service, return them
        if (isset($this->attributes['recipients'])) {
            return $this->attributes['recipients'];
        }
        
        // Otherwise, load them automatically (fallback)
        if (!isset($this->attributes['recipients_loaded'])) {
            $recipientIds = $this->messageRecipients()->pluck('recipient_id');
            $recipients = User::whereIn('id', $recipientIds)->get();
            $this->attributes['recipients'] = $recipients;
            $this->attributes['recipients_loaded'] = true;
        }
        return $this->attributes['recipients'] ?? collect();
    }
    
    // Setter for recipients
    public function setRecipientsAttribute($recipients)
    {
        $this->attributes['recipients'] = $recipients instanceof \Illuminate\Support\Collection 
            ? $recipients 
            : collect($recipients);
    }
}

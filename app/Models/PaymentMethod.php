<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string
     */
    protected $connection = 'payment';

    protected $fillable = [
        'name',
        'code',
        'description',
        'configuration',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

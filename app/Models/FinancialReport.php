<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'total_revenue',
        'total_bookings',
        'total_refunds',
        'report_data',
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'total_revenue' => 'decimal:2',
        'total_bookings' => 'integer',
        'total_refunds' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weekend extends Model
{
    use HasFactory;
    protected $table = 'weekend';

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'total_hours',
        'is_weekend',
    ];
}

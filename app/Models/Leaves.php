<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaves extends Model
{
    use HasFactory;
    protected $table = 'leaves';
    protected $fillable = [
        'user_id',

    ];

    public function employee()
    {
        return $this->belongsTo(User::class);
    }
}

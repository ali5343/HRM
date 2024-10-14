<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leaves::class);
    }

    public function wfh()
    {
        return $this->hasMany(Wfh::class);
    }

    public function overtime()
    {
        return $this->hasMany(Overtime::class);
    }

}

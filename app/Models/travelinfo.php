<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class travelinfo extends Model
{
    use HasFactory;
    protected $fillable = ['travel_mode','travel_date','travel_time'];
}

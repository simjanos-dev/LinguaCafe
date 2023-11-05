<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'achieved_quantity'
    ];
}

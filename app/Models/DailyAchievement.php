<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAchievement extends Model
{
    use HasFactory;
    protected $table = 'daily_achivements';

    protected $fillable = [
        'user_id',
        'day',
        'language',
        'read_words',
        'reivewed_sentences',
    ];
}

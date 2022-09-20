<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAchivement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day',
        'language',
        'read_words',
        'reivewed_sentences',
    ];
}

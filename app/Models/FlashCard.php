<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language',
        'flash_card_collection_id',
        'sentence_raw',
        'sentence_processed',
        'translation',
        'reading',
        'level',
        'last_reviewed',
    ];
}

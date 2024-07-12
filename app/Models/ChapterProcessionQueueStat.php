<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterProcessionQueueStat extends Model
{
    use HasFactory;
    
    protected $table = 'queue_stats_chapter_procession';
}

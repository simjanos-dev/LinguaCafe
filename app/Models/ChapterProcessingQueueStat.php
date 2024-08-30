<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterProcessingQueueStat extends Model
{
    use HasFactory;
    
    protected $table = 'queue_stats_chapter_processing';
}

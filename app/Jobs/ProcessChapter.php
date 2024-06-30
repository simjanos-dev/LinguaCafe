<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

use App\Services\ChapterService;
use App\Models\ChapterProcessionQueueStat;
use App\Models\Chapter;

class ProcessChapter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 1;

    private $userId;
    private $chapterId;
    private $dispatchedAt, $startedAt, $finishedAt;

    public function __construct($userId, $chapterId) {
        $this->userId = $userId;
        $this->chapterId = $chapterId;
        $this->dispatchedAt = Carbon::now();
    }

    public function handle() {
        try {
            $this->startedAt = Carbon::now();
            $wordCount = (new ChapterService())->processChapterText($this->userId, $this->chapterId);
            $this->finishedAt = Carbon::now();
            
            if(rand(1,3) === 2) {
                $a = $wordCount[2];
            }

            $chapterProcessionQueueStat = new ChapterProcessionQueueStat();
            $chapterProcessionQueueStat->user_id = $this->userId;
            $chapterProcessionQueueStat->chapter_id = $this->chapterId;
            $chapterProcessionQueueStat->status = 'finished';
            $chapterProcessionQueueStat->word_count = $wordCount;
            $chapterProcessionQueueStat->dispatched_at = $this->dispatchedAt;
            $chapterProcessionQueueStat->started_at = $this->startedAt;
            $chapterProcessionQueueStat->finished_at = $this->finishedAt;
            $chapterProcessionQueueStat->save();
        } catch (\Throwable $e) {
            $this->jobFailed();
        }        
    }

    /*
        Laravel does not pass context to it's own failed() method....
    */
    public function jobFailed() {
        $chapter = Chapter
            ::where('id', $this->chapterId)
            ->where('user_id', $this->userId)
            ->first();
        
        if (!$chapter) {
            return;
        }

        // set chapter processing status to failed
        $chapter->processing_status = 'failed';
        $chapter->save();

        // create a chapter procession queue stat
        $chapterProcessionQueueStat = new ChapterProcessionQueueStat();
        $chapterProcessionQueueStat->user_id = $this->userId;
        $chapterProcessionQueueStat->chapter_id = $this->chapterId;
        $chapterProcessionQueueStat->status = 'failed';
        $chapterProcessionQueueStat->word_count = 0;
        $chapterProcessionQueueStat->dispatched_at = $this->dispatchedAt;
        $chapterProcessionQueueStat->started_at = $this->startedAt;
        $chapterProcessionQueueStat->finished_at = $this->finishedAt;
        $chapterProcessionQueueStat->save();
    }
}
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

class ProcessChapter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $userId;
    private $chapterId;
    private $dispatchedAt, $startedAt, $finishedAt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $chapterId)
    {
        $this->userId = $userId;
        $this->chapterId = $chapterId;
        $this->dispatchedAt = Carbon::now();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->startedAt = Carbon::now();
        $wordCount = (new ChapterService())->processChapterText($this->userId, $this->chapterId);
        $this->finishedAt = Carbon::now();

        $chapterProcessionQueueStat = new ChapterProcessionQueueStat();
        $chapterProcessionQueueStat->user_id = $this->userId;
        $chapterProcessionQueueStat->chapter_id = $this->chapterId;
        $chapterProcessionQueueStat->word_count = $wordCount;
        $chapterProcessionQueueStat->dispatched_at = $this->dispatchedAt;
        $chapterProcessionQueueStat->started_at = $this->startedAt;
        $chapterProcessionQueueStat->finished_at = $this->finishedAt;
        $chapterProcessionQueueStat->save();
    }
}
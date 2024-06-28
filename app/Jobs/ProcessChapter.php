<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Services\ChapterService;

class ProcessChapter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $userId;
    private $chapterId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $chapterId)
    {
        $this->userId = $userId;
        $this->chapterId = $chapterId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new ChapterService())->processChapterText($this->userId, $this->chapterId);
    }
}

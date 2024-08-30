<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\Phrase;
use App\Models\Chapter;
use Illuminate\Bus\Queueable;
use App\Models\EncounteredWord;

// services
use App\Services\ChapterService;
use Ramsey\Collection\Collection;

// models
use App\Services\QueueStatsService;
use App\Services\VocabularyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessChapter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    

    private VocabularyService $vocabularyService;
    private ChapterService $chapterService;
    private QueueStatsService $queueStatsService;

    private $userId;
    private $userUuid;
    private $chapterId;
    private $language;
    private $dispatchedAt, $startedAt;

    public function __construct(
        $userId, 
        $userUuid, 
        $chapterId, 
        $language
    ) 
    {
        $this->vocabularyService = new VocabularyService();
        $this->chapterService = new ChapterService();
        $this->queueStatsService = new QueueStatsService();

        $this->userId = $userId;
        $this->userUuid = $userUuid;
        $this->chapterId = $chapterId;
        $this->language = $language;
        $this->dispatchedAt = Carbon::now();
    }

    public function handle() {
        try {
            $this->startedAt = Carbon::now();

            $chapter = Chapter::query()
                ->where('id', $this->chapterId)
                ->where('user_id', $this->userId)
                ->first();

            // process chapter text
            $this->chapterService->processChapterText($this->userId, $this->chapterId);
            
            // index phrases that were created while the job was running
            $phrases = Phrase
                ::where('user_id', $this->userId)
                ->where('language', $this->language)
                ->where('created_at', '>=', $this->startedAt)
                ->where('created_at', '<=', Carbon::now())
                ->get();

            foreach ($phrases as $phrase) {
                $this->vocabularyService->indexPhraseInChapter($chapter->id, $this->userId, $this->language, $phrase);
            }

            $chapter->refresh();
            $this->queueStatsService->insertChapterProcessedStat($chapter, 'finished', $this->dispatchedAt, $this->startedAt);
            $this->broadcastChapterStatusEvent($chapter);
        } catch (\Throwable $e) {
            $this->jobFailed();
            throw $e;
        }
    }

    // Laravel does not pass context to it's own failed() method.
    public function jobFailed() 
    {
        $chapter = Chapter
            ::where('id', $this->chapterId)
            ->where('user_id', $this->userId)
            ->first();
        
        // set chapter processing status to failed
        $chapter->processing_status = 'failed';
        $chapter->save();

        $this->queueStatsService->insertChapterProcessedStat($chapter, 'failed', $this->dispatchedAt, $this->startedAt);
        $this->broadcastChapterStatusEvent($chapter);
    }

    private function broadcastChapterStatusEvent(Chapter $chapter): void
    {
        $words = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', $this->userId)
            ->where('language', $this->language)
            ->get()
            ->keyBy('id')
            ->toArray();

        if ($chapter->processing_status === 'processed') {
            $chapter->wordCount = $chapter->getWordCounts($words);
        }
        
        event(new \App\Events\ChapterStateUpdatedEvent($this->userUuid, [
            $chapter->id => [
                'processing_status' => $chapter->processing_status,
                'wordCount' => $chapter->wordCount ?? null,
            ]
        ]));
    }
}
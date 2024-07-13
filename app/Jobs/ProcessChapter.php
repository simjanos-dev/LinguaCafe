<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Phrase;
use App\Models\Chapter;
use Illuminate\Bus\Queueable;
use App\Models\EncounteredWord;
use App\Services\ChapterService;

// services
use App\Services\VocabularyService;
use Illuminate\Queue\SerializesModels;

// models
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ChapterProcessionQueueStat;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessChapter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $userId;
    private $userUuid;
    private $chapterId;
    private $language;
    private $dispatchedAt, $startedAt;

    public function __construct($userId, $userUuid, $chapterId, $language) {
        $this->userId = $userId;
        $this->userUuid = $userUuid;
        $this->chapterId = $chapterId;
        $this->language = $language;
        $this->dispatchedAt = Carbon::now();
    }

    public function handle() {
        try {
            $chapter = Chapter
                ::where('id', $this->chapterId)
                ->where('user_id', $this->userId)
                ->first();
        
            if (!$chapter) {
                return;
            }

            $this->startedAt = Carbon::now();

            // process chapter text
            $wordCount = (new ChapterService())->processChapterText($this->userId, $this->chapterId);
            
            // index phrases that were created while the job was running
            $phrases = Phrase
                ::where('user_id', $this->userId)
                ->where('language', $this->language)
                ->where('created_at', '>=', $this->startedAt)
                ->where('created_at', '<=', Carbon::now())
                ->get();

            foreach ($phrases as $phrase) {
                (new VocabularyService())->indexPhraseInChapter($chapter->id, $this->userId, $this->language, $phrase);
            }

            $chapterProcessionQueueStat = new ChapterProcessionQueueStat();
            $chapterProcessionQueueStat->user_id = $this->userId;
            $chapterProcessionQueueStat->chapter_id = $this->chapterId;
            $chapterProcessionQueueStat->status = 'finished';
            $chapterProcessionQueueStat->word_count = $wordCount;
            $chapterProcessionQueueStat->dispatched_at = $this->dispatchedAt;
            $chapterProcessionQueueStat->started_at = $this->startedAt;
            $chapterProcessionQueueStat->finished_at = Carbon::now();
            $chapterProcessionQueueStat->save();

            $this->broadcastChapterStatusEvent($chapter->book_id);
        } catch (\Throwable $e) {
            $this->jobFailed();
            throw $e;
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
        $chapterProcessionQueueStat->finished_at = Carbon::now();
        $chapterProcessionQueueStat->save();

        $this->broadcastChapterStatusEvent($chapter->book_id);
    }

    private function broadcastChapterStatusEvent($bookId) {
        $words = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', $this->userId)
            ->where('language', $this->language)
            ->get()
            ->keyBy('id')
            ->toArray();

        $chapters = Chapter
            ::select(['id', 'processing_status', 'unique_word_ids', 'word_count'])
            ->where('book_id', $bookId)
            ->where('user_id', $this->userId)
            ->where('processing_status', '<>', 'processing')
            ->get();

        $chapters->map(function (Chapter $chapter) use ($words) {
            if ($chapter->processing_status !== 'processed') {
                return $chapter;
            }

            $chapter->wordCount = $chapter->getWordCounts($words);
            unset($chapter->unique_word_ids);
            unset($chapter->word_count);

            return $chapter;
        });      

        
        event(new \App\Events\ChapterStateUpdatedEvent($this->userUuid, $chapters->keyBy('id')->toArray()));
    }
}
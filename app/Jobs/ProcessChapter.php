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
use App\Services\VocabularyService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ChapterProcessionQueueStat;
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
            // for testing failed jobs
            // if(random_int(1, 2) === 2) {
            //     throw new \Exception('teszt');
            // }

            $chapter = Chapter
                ::where('id', $this->chapterId)
                ->where('user_id', $this->userId)
                ->first();
        
            if (!$chapter) {
                return;
            }

            $this->startedAt = Carbon::now();

            // process chapter text
            (new ChapterService())->processChapterText($this->userId, $this->chapterId);
            
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

        $this->broadcastChapterStatusEvent();
    }

    private function broadcastChapterStatusEvent() {
        $words = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', $this->userId)
            ->where('language', $this->language)
            ->get()
            ->keyBy('id')
            ->toArray();

        $chapter = Chapter
            ::select(['id', 'processing_status', 'unique_word_ids', 'word_count'])
            ->where('id', $this->chapterId)
            ->where('user_id', $this->userId)
            ->where('processing_status', '<>', 'processing')
            ->first();

        if ($chapter->processing_status === 'processed') {
            $chapter->wordCount = $chapter->getWordCounts($words);
        }
        
        unset($chapter->unique_word_ids);
        unset($chapter->word_count);
        
        event(new \App\Events\ChapterStateUpdatedEvent($this->userUuid, [
            $chapter->id => $chapter
        ]));
    }
}
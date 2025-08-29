<?php

namespace App\Services;

use stdClass;
use Exception;

use App\Models\Book;
use App\Models\User;
use App\Models\Phrase;

use App\Models\Chapter;
use Illuminate\Support\Str;
use App\Jobs\ProcessChapter;
use App\Services\BookService;
use App\Services\GoalService;
use App\Models\EncounteredWord;
use App\Services\TextBlockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\Language\LanguageConfig;
use App\Enums\ChapterProcessingStatusEnum;

class ChapterService {
    private $bookService;

    public function __construct() {
        $this->bookService = new BookService();
    }

    public function getChaptersForBook(User $user, Book $book): Collection
    {
        if ($book->user_id !== $user->id) {
            throw new Exception('Book not found or unauthorized.');
        }

        $chapters = Chapter::query()
            ->select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids', 'processing_status'])
            ->where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->get();
 
            $chapters->transform(function(Chapter $chapter) {
                $chapter->wordCount = [
                    'total' => $chapter->word_count,
                    'unique' => null,
                    'known' => null,
                    'highlighted' => null,
                    'new' => null,
                ];

                return $chapter;
            });
 
        return $chapters;
    }

    // Function that returns the different word counts for all chapters in an array
    public function getChaptersWordsCount(User $user, Book $book): array
    {
        if ($book->user_id !== $user->id) {
            throw new Exception('Book not found or unauthorized.');
        }

        $chapters = Chapter::query()
            ->where('book_id', $book->id)
            ->where('user_id', $user->id)
            ->where('processing_status', ChapterProcessingStatusEnum::PROCESSED->value)
            ->get();

        $words = EncounteredWord::query()
            ->select(['id', 'word', 'stage'])
            ->where('user_id', $user->id)
            ->where('language', $book->language)
            ->get()
            ->keyBy('id')
            ->toArray();


        $chaptersWithWordCounts = [];
        foreach ($chapters as $chapter) {
            $chapterData = [
                'wordCount' => $chapter->getWordCounts($words),
                'processing_status' => $chapter->processing_status,
            ];
            $chaptersWithWordCounts[$chapter->id] = $chapterData;
        }

        return $chaptersWithWordCounts;
    }
    
    public function getChapterForEditor(User $user, Chapter $chapter): Chapter 
    {
        if ($chapter->user_id !== $user->id) {
            throw new Exception('Chapter not found or unauthorized.');
        }

        if ($chapter->processing_status !== ChapterProcessingStatusEnum::PROCESSED->value) {
            throw new Exception('Chapter is not processed.');
        }

        $transformedRawText = Str::replace(" NEWLINE \r\n", "\r\n", $chapter->raw_text);
        $chapter->raw_text = $transformedRawText;
        $chapter->makeHidden('processed_text');
        
        return $chapter;
    }

    public function getChapterForReader(User $user, LanguageConfig $language, Chapter $chapter): stdClass
    {
        
        if ($chapter->user_id !== $user->id) {
            throw new Exception('Chapter not found or unauthorized.');
        }

        if ($chapter->processing_status !== ChapterProcessingStatusEnum::PROCESSED->value) {
            throw new Exception('Chapter is not processed.');
        }

        $book = Book::query()
            ->where('id', $chapter->book_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $chapters = Chapter::query()
            ->select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids', 'processing_status'])
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->get();

        $words = $chapter->getProcessedText();

        $uniqueWordsForWordCounts = EncounteredWord::query()
            ->select(['id', 'word', 'stage', 'image'])
            ->where('user_id', $user->id)
            ->where('language', $chapter->language)
            ->get()
            ->keyBy('id')
            ->toArray();

        $chapters->transform(function(Chapter $chapter) use($uniqueWordsForWordCounts) {
            $chapter->wordCount = [
                'total' => $chapter->word_count,
                'unique' => null,
                'known' => null,
                'highlighted' => null,
                'new' => null,
            ];
                        
            if ($chapter->processing_status !== ChapterProcessingStatusEnum::PROCESSED->value) {
                return $chapter;
            }

            $chapter->wordCount = $chapter->getWordCounts($uniqueWordsForWordCounts);
            
            return $chapter;
        });

        $textBlock = new TextBlockService($user->id, $language->name);
        $textBlock->setProcessedWords($words);
        $textBlock->collectUniqueWords();
        $textBlock->prepareTextForReader();
        $textBlock->indexPhrases();

        $data = new stdClass();
        $data->type = $chapter->type;
        $data->subtitleTimestamps = $chapter->subtitle_timestamps;
        $data->words = $textBlock->words;
        $data->uniqueWords = $textBlock->uniqueWords;
        $data->phrases = $textBlock->phrases;
        $data->bookName = $book->name;
        $data->chapterId = $chapter->id;
        $data->chapterName = $chapter->name;
        $data->bookId = $book->id;
        $data->language = $chapter->language;
        $data->languageSpaces = $language->hasSpaces();
        $data->chapters = $chapters;
        $data->wordCount = $chapter->word_count;
        
        return $data;
    }

    public function finishChapter(
        User $user, 
        Chapter $chapter, 
        bool $autoMoveWordsToKnown, 
        array $uniqueWords, 
        bool $autoLevelUpWords, 
        array $leveledUpWords, 
        array $leveledUpPhrases
    ): void 
    {
        // automove words that the user sees the first time,
        // but they already know it to learned stage.
        DB::beginTransaction();
        if ($autoMoveWordsToKnown) {
            foreach ($uniqueWords as $uniqueWordData) {
                $saveData = [];
                $saveData['read_count'] = $uniqueWordData->read_count;
                
                if ($uniqueWordData->stage == 2) {
                    $saveData['stage'] = 0;
                }

                EncounteredWord::query()
                    ->where('id', $uniqueWordData->id)
                    ->where('user_id', $user->id)
                    ->update($saveData);
            }
        }

        DB::commit();

        $chapter->read_count ++;
        $chapter->save();

        // updage today's reading achievement
        (new GoalService())->updateGoalAchievement($user->id, $user->selected_language, 'read_words', $chapter->word_count);

        // level up phrases
        if (!$autoLevelUpWords) {
            return;
        }

        foreach ($leveledUpPhrases as $phraseId) {
            $phrase = Phrase::query()
                ->where('id', $phraseId)
                ->where('user_id', $user->id)
                ->where('language', $user->selected_language)
                ->firstOrFail();

            $phrase->setStage($phrase->stage + 1);
            $phrase->save();
        }

        // level up words
        foreach ($leveledUpWords as $wordId) {
            $word = EncounteredWord::query()
                ->where('id', $wordId)
                ->where('user_id', $user->id)
                ->where('language', $user->selected_language)
                ->firstOrFail();

            $word->setStage($word->stage + 1);
            $word->save();  
        }
    }

    public function createChapter(User $user, Book $book, string $name, string $text) {

        if ($book->user_id !== $user->id) {
            throw new Exception('Book not found or unauthorized.');
        }

        $chapter = new Chapter();
        $chapter->user_id = $user->id;
        $chapter->processing_status = ChapterProcessingStatusEnum::UNPROCESSED->value;
        $chapter->name = $name;
        $chapter->type = 'text';
        $chapter->subtitle_timestamps = '';
        $chapter->read_count = 0;
        $chapter->word_count = 0;
        $chapter->book_id = $book->id;
        $chapter->language = $book->language;
        $chapter->unique_words = '';
        $chapter->save();
        
        $this->updateChapter($user, $chapter, $name, $text);
        
        return true;
    }

    // updates the name and text of a chapter
    public function updateChapter(User $user, Chapter $chapter, string $name, string $text): void
    {
        if ($chapter->user_id !== $user->id) {
            throw new Exception('Chapter not found or unauthorized.');
        }

        DB::disableQueryLog();
        
        $chapter->raw_text = $text;
        $chapter->name = $name;
        $chapter->processing_status = ChapterProcessingStatusEnum::UNPROCESSED->value;
        $chapter->save();
        
        ProcessChapter::dispatch($user->id, $user->uuid, $chapter->id, $chapter->language);
    }

    // TODO: this method should be moved into its own ChapterProcessing service, together with retryFailedChapters
    public function processChapterText($userId, $chapterId) {
        DB::disableQueryLog();
        $bookId = null;

        DB::transaction(function() use(&$bookId, $userId, $chapterId) {
            // retrieve chapter
            $chapter = Chapter
                ::lockForUpdate()
                ->where('id', $chapterId)
                ->where('user_id', $userId)
                ->first();

            if (!$chapter) {
                throw new Exception('Chapter does not exist, or it belongs to a different user.');
            }
            
            // process text
            $textBlock = new TextBlockService($userId, $chapter->language);        
            
            if ($chapter->type == 'text') {
                $textBlock->rawText = $chapter->raw_text;
                $textBlock->tokenizeRawText();
                $timeStamps = [];
            } else {
                $textBlock->rawText = $chapter->raw_text;
                $timeStamps = $textBlock->tokenizeRawSubtitles();
            }
            
            $textBlock->processTokenizedWords();
            $textBlock->collectUniqueWords();
            $textBlock->updateAllPhraseIds();
            $textBlock->createNewEncounteredWords();

            // collect unique word ID-s
            $uniqueWordIds = DB
                ::table('encountered_words')
                ->select('id')
                ->where('user_id', $userId)
                ->where('language', $chapter->language)
                ->whereIn('word', $textBlock->uniqueWords)
                ->pluck('id')
                ->toArray();

            // update chapter word data
            $chapter->word_count = $textBlock->getWordCount();
            $chapter->unique_words = json_encode($textBlock->uniqueWords);
            $chapter->unique_word_ids = json_encode($uniqueWordIds);
            $chapter->setProcessedText($textBlock->processedWords);
            $chapter->subtitle_timestamps = json_encode($timeStamps);
            $chapter->processing_status = ChapterProcessingStatusEnum::PROCESSED->value;
            $chapter->save();
            
            $bookId = $chapter->book_id;    
        });
        
        $this->bookService->updateBookWordCount($userId, $bookId);
    }

    public function deleteChapter(User $user, Chapter $chapter): void
    {
        
        if ($chapter->user_id !== $user->id) {
            throw new Exception('Chapter not found or unauthorized.');
        }

        $chapter->delete();

        $this->bookService->updateBookWordCount($user->id, $chapter->book_id);
    }

    public function retryFailedChapters(User $user, Book $book) {
        $chapters = Chapter::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('processing_status', ChapterProcessingStatusEnum::FAILED->value)
            ->get();

        $chapters->each(function(Chapter $chapter) use($user) {
            $chapter->processing_status = ChapterProcessingStatusEnum::UNPROCESSED->value;
            $chapter->save();

            \App\Jobs\ProcessChapter::dispatch($user->id, $user->uuid, $chapter->id, $chapter->language);
        });

        return true;
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Services\GoalService;

use App\Models\Book;
use App\Models\Lesson;
use App\Models\EncounteredWord;
use App\Models\TextBlock;


class ChapterService {
    
    public function __construct() {

    }

    public function getChaptersForBook($userId, $bookId) {
        $book = Book
            ::where('id', $bookId)
            ->where('user_id', $userId)
            ->first();
        
        if (!$book) {
            throw new \Exception('Book does not exist, or it belongs to a different user.');
        }

        $chapters = Lesson
            ::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])
            ->where('book_id', $bookId)
            ->where('user_id', $userId)
            ->get();

        $words = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', $userId)
            ->where('language', $book->language)
            ->get()
            ->keyBy('id')
            ->toArray();

        for ($i = 0; $i < count($chapters); $i++) {
            $chapters[$i]->wordCount = $chapters[$i]->getWordCounts($words);
        }
        
        $data = new \stdClass();
        $data->book = $book;
        $data->chapters = $chapters;

        return $data;
    }

    public function getChapterForEditor($userId, $chapterId) {
        $chapter = Lesson::
            select(['name', 'raw_text', 'type'])
            ->where('id', $chapterId)
            ->where('user_id', $userId)
            ->first();

        if (!$chapter) {
            throw new \Exception('Chapter does not exist, or it belongs to a different user.');
        }

        $chapter->raw_text = str_replace(" NEWLINE \r\n", "\r\n", $chapter->raw_text);
        
        return $chapter;
    }

    public function getChapterForReader($userId, $chapterId) {
        $chapter = Lesson
            ::where('id', $chapterId)
            ->where('user_id', $userId)
            ->first();
        
        if (!$chapter) {
            throw new \Exception('Chapter does not exist, or it belongs to a different user.');
        }

        $book = Book
            ::where('id', $chapter->book_id)
            ->where('user_id', $userId)
            ->first();

        $chapters = Lesson
            ::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])
            ->where('user_id', $userId)
            ->where('book_id', $book->id)
            ->get();

        $words = $chapter->getProcessedText();

        // get lesson word counts
        $uniqueWordsForWordCounts = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', $userId)
            ->where('language', $chapter->language)
            ->get()
            ->keyBy('id')
            ->toArray();

        for ($i = 0; $i < count($chapters); $i++) {
            $chapters[$i]->wordCount = $chapters[$i]->getWordCounts($uniqueWordsForWordCounts);
        }

        $textBlock = new TextBlock();
        $textBlock->setProcessedWords($words);
        $textBlock->collectUniqueWords();
        $textBlock->prepareTextForReader();
        $textBlock->indexPhrases();

        $data = new \stdClass();
        $data->type = $chapter->type;
        $data->subtitleTimestamps = $chapter->subtitle_timestamps;
        $data->words = $textBlock->words;
        $data->uniqueWords = $textBlock->uniqueWords;
        $data->phrases = $textBlock->phrases;
        $data->bookName = $book->name;
        $data->lessonId = $chapter->id;
        $data->lessonName = $chapter->name;
        $data->bookId = $book->id;
        $data->language = $chapter->language;
        $data->lessons = $chapters;
        $data->wordCount = $chapter->word_count;
        
        return $data;
    }

    public function finishChapter($userId, $chapterId, $autoMoveWordsToKnown, $uniqueWords, $language) {
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

                EncounteredWord
                    ::where('id', $uniqueWordData->id)
                    ->where('user_id', $userId)
                    ->update($saveData);
            }
        }

        DB::commit();

        // increase chapter read count
        $chapter = Lesson
            ::where('id', $chapterId)
            ->where('user_id', $userId)
            ->first();

        if (!$chapter) {
            throw new \Exception('Chapter does not exist, or it belongs to a different user.');
        }

        $chapter->read_count ++;
        $chapter->save();

        // updage today's reading achievement
        (new GoalService())->updateGoalAchievement($userId, $language, 'read_words', $chapter->word_count);

        return true;
    }

    public function createChapter($userId, $bookId, $chapterName, $chapterText) {

        // retrieve book
        $book = Book
            ::where('id', $bookId)
            ->where('user_id', $userId)
            ->first();

        if (!$book) {
            throw new \Exception('Book does not exist, or it belongs to a different user.');
        }

        $chapter = new Lesson();
        $chapter->user_id = $userId;
        $chapter->name = $chapterName;
        $chapter->type = 'text';
        $chapter->subtitle_timestamps = '';
        $chapter->read_count = 0;
        $chapter->word_count = 0;
        $chapter->book_id = $bookId;
        $chapter->language = $book->language;
        $chapter->unique_words = '';
        $chapter->save();

        $this->updateChapter($userId, $chapter->id, $chapter->name, $chapterText);
        
        return true;
    }

    // updates the name and text of a chapter
    public function updateChapter($userId, $chapterId, $chapterName, $chapterText) {
        \DB::disableQueryLog();
        
        // retrieve chapter
        $chapter = Lesson
            ::where('id', $chapterId)
            ->where('user_id', $userId)
            ->first();

        if (!$chapter) {
            throw new \Exception('Chapter does not exist, or it belongs to a different user.');
        }
        
        // update chapter data
        $chapter->raw_text = $chapterText;
        $chapter->name = $chapterName;
        
        // process text
        $textBlock = new TextBlock();
        $textBlock->rawText = $chapterText;
        $textBlock->tokenizeRawText();
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

        // update lesson word data
        $chapter->word_count = $textBlock->getWordCount();
        $chapter->unique_words = json_encode($textBlock->uniqueWords);
        $chapter->unique_word_ids = json_encode($uniqueWordIds);
        $chapter->setProcessedText($textBlock->processedWords);
        $chapter->save();

        // calculate book word count
        $bookWordCount = Lesson
            ::where('user_id', $userId)
            ->where('book_id', $chapter->book_id)
            ->sum('word_count');

        $bookWordCount = intval($bookWordCount);

        // update book word count
        Book
            ::where('user_id', $userId)
            ->where('id', $chapter->book_id)
            ->update(['word_count' => $bookWordCount]);

        return true;
    }

    public function deleteChapter($userId, $chapterId) {
        
        // retrieve chapter
        $chapter = Lesson
            ::where('user_id', $userId)
            ->where('id', $chapterId)
            ->first();

        // check if chapter is found
        if (!$chapter) {
            throw new \Exception('Chapter does not exist, or it belongs to a different user.');
        }

        // delete chapter
        $chapter->delete();

        return true;
    }
}
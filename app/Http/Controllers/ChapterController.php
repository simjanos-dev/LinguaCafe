<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\Phrase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\TextBlock;

class ChapterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getChapters(Request $request) {
        $bookId = intval($request->bookId);
        $book = Book
            ::where('id', $bookId)
            ->where('user_id', Auth::user()->id)
            ->first();
        
        $chapters = Lesson
            ::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])
            ->where('book_id', $bookId)
            ->where('user_id', Auth::user()->id)
            ->get();

        $words = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', Auth::user()->id)
            ->where('language', Auth::user()->selected_language)
            ->get()
            ->keyBy('id')
            ->toArray();

        $book->wordCount = $book->getWordCounts($words);
        foreach ($chapters as $chapter) {
            $chapter->wordCount = $chapter->getWordCounts($words);
        }
        
        $data = new \stdClass();
        $data->book = $book;
        $data->chapters = $chapters;

        return json_encode($data);
    }

    public function getChapterForEdit($chapterId) {
        $chapter = Lesson::select(['name', 'raw_text'])->where('id', $chapterId)->where('user_id', Auth::user()->id)->first();
        $chapter->raw_text = str_replace(" NEWLINE \r\n", "\r\n", $chapter->raw_text);
        return $chapter;
    }

    public function getChapterForReader(Request $request) 
    {        
        $lessonId = $request->chapterId;
        $wordsToSkip = config('langapp.wordsToSkip');
        $selectedLanguage = Auth::user()->selected_language;
        

        $lesson = Lesson::where('id', $lessonId)->where('user_id', Auth::user()->id)->first();
        $uniqueWords = json_decode($lesson->unique_words);
        $book = Book::where('id', $lesson->book_id)->where('user_id', Auth::user()->id)->first();
        $lessons = Lesson::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])->where('book_id', $book->id)->where('user_id', Auth::user()->id)->get();
        $words = $lesson->getProcessedText();

        // get lesson word counts
        $uniqueWordsForWordCounts = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->get()
            ->keyBy('id')
            ->toArray();

        foreach ($chapters as $chapter) {
            $chapter->wordCount = $chapter->getWordCounts($uniqueWordsForWordCounts);
        }

        $textBlock = new TextBlock();
        $textBlock->setProcessedWords($words);
        $textBlock->collectUniqueWords();
        $textBlock->prepareTextForReader();
        $textBlock->indexPhrases();

        $data = new \stdClass();
        $data->words = $textBlock->words;
        $data->uniqueWords = $textBlock->uniqueWords;
        $data->phrases = $textBlock->phrases;
        $data->bookName = $book->name;
        $data->lessonId = $lesson->id;
        $data->lessonName = $lesson->name;
        $data->bookId = $book->id;
        $data->language = $lesson->language;
        $data->lessons = $lessons;
        $data->wordCount = $lesson->word_count;
        
        
        return json_encode($data);
    }

    public function finishChapter(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $uniqueWords = json_decode($request->uniqueWords);
        $phrases = json_decode($request->phrases);
        $deletedPhrases = json_decode($request->deletedPhrases);
        $autoMoveWordsToKnown = boolval($request->autoMoveWordsToKnown);
        $today = date('Y-m-d');
        
        // automove words that the user sees the first time in the software,
        // but they already know it to learned stage.
        DB::beginTransaction();
        if ($autoMoveWordsToKnown) {
            foreach ($uniqueWords as $uniqueWordData) {
                $saveData = [];
                $saveData['read_count'] = $uniqueWordData->read_count;
                
                if ($uniqueWordData->stage == 2) {
                    $saveData['stage'] = 0;
                    $saveData['added_to_srs'] = null;
                    $saveData['next_review'] = null;
                    $saveData['relearning'] = false;
                }

                EncounteredWord::where('id', $uniqueWordData->id)->update($saveData);
            }
        }

        DB::commit();

        // increase lesson read count
        $lesson = Lesson::where('id', $request->lessonId)->where('user_id', Auth::user()->id)->first();
        $lesson->read_count ++;
        $lesson->save();

        // update today's reading achievement
        $goal = Goal::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->where('type', 'read_words')
            ->first();
        
        $achievement = GoalAchievement::where('user_id', Auth::user()->id)
        ->where('language', $selectedLanguage)
        ->where('goal_id', $goal->id)
        ->where('day', Carbon::now()->toDateString())
        ->first();

        if (!$achievement) {
            $achievement = new GoalAchievement();
            $achievement->language = $selectedLanguage;
            $achievement->user_id = Auth::user()->id;
            $achievement->goal_id = $goal->id;
            $achievement->achieved_quantity = 0;
            $achievement->goal_quantity = $goal->quantity;
            $achievement->day = Carbon::now()->toDateString();
        }
        

        $achievement->achieved_quantity += $lesson->word_count;
        $achievement->save();
        
        return 'success';
    }

    public function saveChapter(Request $request) {
        \DB::disableQueryLog();
        $selectedLanguage = Auth::user()->selected_language;
        
        // retrieve lesson
        if (isset($request->lesson_id)) {
            $lesson = Lesson::where('id', $request->lesson_id)->where('user_id', Auth::user()->id)->first();
        } else {
            $lesson = new Lesson();
        }
        
        // set lesson data from post data
        $lesson->user_id = Auth::user()->id;
        $lesson->name = $request->name;
        $lesson->read_count = isset($request->lesson_id) ? $lesson->read_count : 0;
        $lesson->word_count = 0;
        $lesson->book_id = $request->book;
        $lesson->language = $selectedLanguage;
        $lesson->raw_text = $request->raw_text;
        $lesson->unique_words = '';
        $lesson->save();
        
        $textBlock = new TextBlock();
        $textBlock->rawText = $request->raw_text;
        $textBlock->tokenizeRawText();
        $textBlock->processTokenizedWords();
        $textBlock->collectUniqueWords();
        $textBlock->updateAllPhraseIds();
        $textBlock->createNewEncounteredWords();

        $uniqueWordIds = DB
            ::table('encountered_words')
            ->select('id')
            ->where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->whereIn('word', $textBlock->uniqueWords)
            ->pluck('id')
            ->toArray();

        // update lesson word data
        $lesson->word_count = $textBlock->getWordCount();
        $lesson->unique_words = json_encode($textBlock->uniqueWords);
        $lesson->unique_word_ids = json_encode($uniqueWordIds);
        $lesson->setProcessedText($textBlock->processedWords);
        $lesson->save();

        // update book word count
        $bookWordCount = intval(Lesson::where('user_id', Auth::user()->id)->where('book_id', $lesson->book_id)->sum('word_count'));
        Book::where('user_id', Auth::user()->id)->where('id', $lesson->book_id)->update(['word_count' => $bookWordCount]);

        return 'success';
    }

    public function deleteChapter(Request $request) {
        $chapterId = $request->post('chapterId');
        $userId = Auth::user()->id;

        Lesson
            ::where('user_id', $userId)
            ->where('id', $chapterId)
            ->delete();

        return 'success';
    }
}
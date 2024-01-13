<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Chapter;
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
        
        $chapters = Chapter
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
        for ($i = 0; $i < count($chapters); $i++) {
            $chapters[$i]->wordCount = $chapters[$i]->getWordCounts($words);
        }
        
        $data = new \stdClass();
        $data->book = $book;
        $data->chapters = $chapters;

        return json_encode($data);
    }

    public function getChapterForEdit($chapterId) {
        $chapter = Chapter::select(['name', 'raw_text'])->where('id', $chapterId)->where('user_id', Auth::user()->id)->first();
        $chapter->raw_text = str_replace(" NEWLINE \r\n", "\r\n", $chapter->raw_text);
        return $chapter;
    }

    public function getChapterForReader(Request $request) 
    {        
        $chapterId = $request->chapterId;
        $wordsToSkip = config('langapp.wordsToSkip');
        $selectedLanguage = Auth::user()->selected_language;
        

        $chapter = Chapter::where('id', $chapterId)->where('user_id', Auth::user()->id)->first();
        $uniqueWords = json_decode($chapter->unique_words);
        $book = Book::where('id', $chapter->book_id)->where('user_id', Auth::user()->id)->first();
        $chapters = Chapter::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])->where('book_id', $book->id)->where('user_id', Auth::user()->id)->get();
        $words = $chapter->getProcessedText();

        // get chapter word counts
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
        $data->chapterId = $chapter->id;
        $data->chapterName = $chapter->name;
        $data->bookId = $book->id;
        $data->language = $chapter->language;
        $data->chapters = $chapters;
        $data->wordCount = $chapter->word_count;
        
        
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

        // increase chapter read count
        $chapter = Chapter::where('id', $request->chapterId)->where('user_id', Auth::user()->id)->first();
        $chapter->read_count ++;
        $chapter->save();

        // updage today's reading achievement
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
        

        $achievement->achieved_quantity += $chapter->word_count;
        $achievement->save();
        
        return 'success';
    }

    public function saveChapter(Request $request) {
        \DB::disableQueryLog();
        $selectedLanguage = Auth::user()->selected_language;
        
        // retrieve chapter
        if (isset($request->chapter_id)) {
            $chapter = Chapter::where('id', $request->chapter_id)->where('user_id', Auth::user()->id)->first();
        } else {
            $chapter = new Chapter();
        }
        
        // set chapter data from post data
        $chapter->user_id = Auth::user()->id;
        $chapter->name = $request->name;
        $chapter->read_count = isset($request->chapter_id) ? $chapter->read_count : 0;
        $chapter->word_count = 0;
        $chapter->book_id = $request->book;
        $chapter->language = $selectedLanguage;
        $chapter->raw_text = $request->raw_text;
        $chapter->unique_words = '';
        $chapter->save();
        
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

        // update chapter word data
        $chapter->word_count = $textBlock->getWordCount();
        $chapter->unique_words = json_encode($textBlock->uniqueWords);
        $chapter->unique_word_ids = json_encode($uniqueWordIds);
        $chapter->setProcessedText($textBlock->processedWords);
        $chapter->save();

        // update book word count
        $bookWordCount = intval(Chapter::where('user_id', Auth::user()->id)->where('book_id', $chapter->book_id)->sum('word_count'));
        Book::where('user_id', Auth::user()->id)->where('id', $chapter->book_id)->update(['word_count' => $bookWordCount]);

        return 'success';
    }

    public function deleteChapter(Request $request) {
        $chapterId = $request->post('chapterId');
        $userId = Auth::user()->id;

        Chapter
            ::where('user_id', $userId)
            ->where('id', $chapterId)
            ->delete();

        return 'success';
    }
}
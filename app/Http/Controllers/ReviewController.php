<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Phrase;
use App\Models\EncounteredWord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Goal;
use App\Models\GoalAchievement;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function review(Request $request) {
        $practiceMode = false;
        $lessonId = -1;
        $bookId = -1;
        
        if (isset($request->practiceMode)) {
            $practiceMode = $request->practiceMode === 'true';
        }
        
        if (isset($request->lessonId)) {
            $lessonId = intval($request->lessonId);
        }

        if (isset($request->bookId)) {
            $bookId = intval($request->bookId);
        }

        $selectedLanguage = Auth::user()->selected_language;
        $today = date('Y-m-d');

        // base query
        $reviewWords = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0');
        $reviewPhrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0');

        // practice mode
        if (!$practiceMode) {
            $reviewWords = $reviewWords->where(function($query) {
                $query->whereDate('next_review', '<=', Carbon::today()->toDateString());
                $query->orWhere('relearning', true);
            });

            $reviewPhrases = $reviewPhrases->where(function($query) {
                $query->whereDate('next_review', '<=', Carbon::today()->toDateString());
                $query->orWhere('relearning', true);
            });
        }
        
        // limit query to 1 lesson
        if ($lessonId !== -1) {
            $lesson = Lesson::where('id', $lessonId)->where('user_id', Auth::user()->id)->first();            
            $words = json_decode(gzuncompress($lesson->processed_text));
            $uniqueWords = [];
            $uniquePhraseIds = [];

            foreach ($words as $word) {
                if (!in_array(mb_strtolower($word->word), $uniqueWords, true)) {
                    array_push($uniqueWords, mb_strtolower($word->word, 'UTF-8'));
                }

                foreach ($word->phraseIds as $phraseId) {
                    if (!in_array($phraseId, $uniquePhraseIds, true)) {
                        array_push($uniquePhraseIds, $phraseId);
                    }
                }
            }

            $reviewWords = $reviewWords->whereIn('word', $uniqueWords);
            $reviewPhrases = $reviewPhrases->whereIn('id', $uniquePhraseIds);
        }
        
        // limit query to one book
        if ($bookId !== -1 && $lessonId == -1) {
            $uniqueWords = [];
            $uniquePhraseIds = [];
            $lessons = Lesson::where('book_id', $bookId)->where('user_id', Auth::user()->id)->get();
            foreach ($lessons as $lesson) {
                $words = json_decode(gzuncompress($lesson->processed_text));
                foreach ($words as $word) {
                    if (!in_array(mb_strtolower($word->word, 'UTF-8'), $uniqueWords, true)) {
                        array_push($uniqueWords, mb_strtolower($word->word, 'UTF-8'));
                    }
                    
                    foreach ($word->phraseIds as $phraseId) {
                        if (!in_array($phraseId, $uniquePhraseIds, true)) {
                            array_push($uniquePhraseIds, $phraseId);
                        }
                    }
                }
            }

            $reviewWords = $reviewWords->whereIn('word', $uniqueWords);
            $reviewPhrases = $reviewPhrases->whereIn('id', $uniquePhraseIds);
        }

        $reviewWords = $reviewWords->inRandomOrder()->get();
        $reviewPhrases = $reviewPhrases->inRandomOrder()->get();

        $reviews = [];
        foreach ($reviewWords as $word) {
            $word->type = 'word';
            array_push($reviews, $word);
        }

        foreach ($reviewPhrases as $phrase) {
            $phrase->type = 'phrase';
            array_push($reviews, $phrase);
        }

        $data = new \StdClass();
        $data->reviews = $reviews;
        $data->language = $selectedLanguage;

        return json_encode($data);
    }

    public function updateReviewCounts(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        
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
        

        $achievement->achieved_quantity += $request->readWords;
        $achievement->save();

        //$request->readWords

        return 'success';
    }
}

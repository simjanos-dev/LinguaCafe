<?php

namespace App\Http\Controllers;

use App\Models\ExampleSentence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Chapter;
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
        $chapterId = -1;
        $bookId = -1;
        
        if (isset($request->practiceMode)) {
            $practiceMode = $request->practiceMode === 'true';
        }
        
        if (isset($request->chapterId)) {
            $chapterId = intval($request->ChapterId);
        }

        if (isset($request->bookId)) {
            $bookId = intval($request->bookId);
        }

        $selectedLanguage = Auth::user()->selected_language;

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
        
        // Retrieve chapter words and phrases by chapter id.
        $uniqueWords = [];
        $uniquePhraseIds = [];
        if ($chapterId !== -1 || $bookId !== -1) {
            if ($chapterId !== -1) {
                $chapterIds = Chapter
                    ::where('id', $chapterId)
                    ->where('user_id', Auth::user()->id)
                    ->pluck('id')
                    ->toArray();
            } else {
                $chapterIds = Chapter
                    ::where('book_id', $bookId)
                    ->where('user_id', Auth::user()->id)
                    ->pluck('id')
                    ->toArray();
            }

            foreach ($chapterIds as $chapterId) {
                $chapter = Chapter
                    ::where('user_id', Auth::user()->id)
                    ->where('id', $chapterId)
                    ->first();

                $words = $chapter->getProcessedText();
                
                foreach ($words as $word) {
                    if (!in_array(mb_strtolower($word->word), $uniqueWords, true)) {
                        array_push($uniqueWords, mb_strtolower($word->word, 'UTF-8'));
                    }

                    foreach ($word->phrase_ids as $phraseId) {
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

        $data = new \stdClass();
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

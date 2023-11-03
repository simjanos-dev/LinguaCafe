<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ExampleSentence;
use App\Models\EncounteredWord;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\Phrase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dev() {
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $selectedLanguage = Auth::user()->selected_language;
        
        return view('home', [
            'language' => $selectedLanguage
        ]);
    }

    public function getStatistics() {
        // language statistics
        $today = date('Y-m-d');
        $selectedLanguage = Auth::user()->selected_language;
        $languageStatistics = new \stdClass();

        $readingGoal = Goal::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->where('type', 'read_words')
            ->first();

        $languageStatistics->days = new \stdClass();
        $languageStatistics->days->name = 'Days of activity';
        $languageStatistics->days->value = GoalAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->distinct('day')->count('day');
        $languageStatistics->days->color = 'statisticsDays';
        $languageStatistics->days->icon = 'mdi-calendar-check';

        $languageStatistics->readWordCount = new \stdClass();
        $languageStatistics->readWordCount->name = 'Read words';
        $languageStatistics->readWordCount->value = GoalAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('goal_id', $readingGoal->id)->sum('achieved_quantity');
        $languageStatistics->readWordCount->color = 'statisticsReadWords';
        $languageStatistics->readWordCount->icon = 'mdi-book-open-variant';

        if ($selectedLanguage == 'japanese') {
            // get unique kanji
            $uniqueKanji = [];
            $words = EncounteredWord::where('stage', '<=', 0)->where('language', 'japanese')->where('user_id', Auth::user()->id)->get();
            foreach ($words as $word) {
                $kanji = preg_split("//u", $word->kanji, -1, PREG_SPLIT_NO_EMPTY);
                foreach($kanji as $currentKanji) {
                    if(!in_array($currentKanji, $uniqueKanji, true)) {
                        array_push($uniqueKanji, $currentKanji);
                    }
                }
            }
            
            $languageStatistics->kanji = new \stdClass();
            $languageStatistics->kanji->name = 'Kanji';
            $languageStatistics->kanji->value = count($uniqueKanji);
            $languageStatistics->kanji->color = 'statisticsKanji';
            $languageStatistics->kanji->icon = 'mdi-ideogram-cjk';
        }
        
        $languageStatistics->known = new \stdClass();
        $languageStatistics->known->name = 'Known words';
        $languageStatistics->known->value = EncounteredWord::select('id')->where('stage', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->known->color = 'statisticsKnownWords';
        $languageStatistics->known->icon = 'mdi-credit-card-check';

        $languageStatistics->learning = new \stdClass();
        $languageStatistics->learning->name = 'Words currently studied';
        $languageStatistics->learning->value = EncounteredWord::select('id')->where('stage', '<', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->learning->color = 'statisticsLearningWords';
        $languageStatistics->learning->icon = 'mdi-school';
        
        return json_encode($languageStatistics);
    }

    public function getLanguage() {
        return Auth::user()->selected_language;
    }

    public function changeLanguage($language) {
        $user = Auth::user();
        $user->selected_language = strtolower($language);
        $user->save();

        $goal = Goal
            ::where('user_id', $user->id)
            ->where('language', $language)
            ->first();

        if (!$goal) {
            $goal = new Goal();
            $goal->user_id = $user->id;
            $goal->language = $language;
            $goal->name = 'Reviews';
            $goal->type = 'review';
            $goal->quantity = 0;
            $goal->save();

            $goal = new Goal();
            $goal->user_id = $user->id;
            $goal->language = $language;
            $goal->name = 'Reading';
            $goal->type = 'read_words';
            $goal->quantity = 3000;
            $goal->save();

            $goal = new Goal();
            $goal->user_id = $user->id;
            $goal->language = $language;
            $goal->name = 'New words';
            $goal->type = 'learn_words';
            $goal->quantity = 0;
            $goal->save();
        }
    }
}

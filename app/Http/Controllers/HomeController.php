<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ExampleSentence;
use App\Models\EncounteredWord;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\Phrase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $exampleSentences = ExampleSentence::get();
        
        foreach ($exampleSentences as $exampleSentence) {
            // update phrase ids
            $phrases = Phrase::where('user_id', 1)->where('language', 'japanese')->get();
            foreach($phrases as $phrase) {
                $words = json_decode($exampleSentence->words);
                $uniqueWords = json_decode($exampleSentence->unique_words);
                $phraseWords = array_unique(json_decode($phrase->words));

                // check if the lesson contains the phrase
                // otherwise skip the algorithm. 
                $containesPhrase = true;
                foreach ($phraseWords as $phraseWord) {
                    if (!in_array($phraseWord, $uniqueWords, true)) {
                        $containesPhrase = false;
                        break;
                    }
                }

                if (!$containesPhrase) {
                    continue;
                }

                //echo('<pre>');var_dump($words);echo('</pre>');exit;
                // update phrase ids of the lesson
                $exampleSentence->updatePhraseIds($phrase->id, $words);
                $exampleSentence->words = json_encode($words);
                $exampleSentence->save();
            }
        }
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

    public function getGoals() {
        $selectedLanguage = Auth::user()->selected_language;
        $goals = Goal::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();

        foreach ($goals as $goal) {
            $goal->todaysQuantity = $goal->getTodaysQuantity();
        }

        return json_encode($goals);
    }

    public function getCalendarData() {
        $selectedLanguage = Auth::user()->selected_language;
        $calendarData = [];

        // query goal achievements
        $goalAchievements = GoalAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        $goalAchievements = DB::table('goal_achievements')
            ->leftJoin('goals', 'goal_achievements.goal_id', '=', 'goals.id')
            ->select('goals.name', 'goals.type', 'goal_achievements.day', 'goal_achievements.achieved_quantity', 'goal_achievements.goal_quantity')
            ->where('goals.user_id', Auth::user()->id)
            ->where('goals.language', $selectedLanguage)->get();

        // add goal achievements to calendar data
        foreach ($goalAchievements as $achievement) {
            // look for achievement date in calendar data
            $dayIndex = -1;
            foreach ($calendarData as $index => $day) {
                if ($day->day == $achievement->day) {
                    $dayIndex = $index;
                    break;
                }
            }

            // update or append calendar data
            $achievementData = new \StdClass();
            $achievementData->name = $achievement->name;
            $achievementData->type = $achievement->type;
            $achievementData->day = $achievement->day;
            $achievementData->achievedQuantity = $achievement->achieved_quantity;
            $achievementData->goalQuantity = $achievement->goal_quantity;
            
            if ($dayIndex !== -1) {
                array_push($calendarData[$dayIndex]->achievements, $achievementData);
            } else {
                $dayData = new \StdClass();
                $dayData->day = $achievement->day;
                $dayData->achievements = [$achievementData];
                $dayData->reviewsDue = 0;
                array_push($calendarData, $dayData);
            }
        }

        // query the count of reviews for each day
        $reviewsDue = EncounteredWord::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->whereNotNull('next_review')
            ->selectRaw(DB::raw('next_review as day, count(id) as quantity'))
            ->groupBy('next_review')->get();


        // add reviews due to calendar data
        foreach ($reviewsDue as $review) {
            // look for review date in calendar data
            $dayIndex = -1;
            foreach ($calendarData as $index => $day) {
                if ($day->day == $review->day) {
                    $dayIndex = $index;
                    break;
                }
            }

            // update or append calendar data
            if ($dayIndex !== -1) {
                $calendarData[$dayIndex]->reviewsDue = $review->quantity;
            } else {
                $dayData = new \StdClass();
                $dayData->day = $review->day;
                $dayData->achievements = [];
                $dayData->reviewsDue = $review->quantity;
                array_push($calendarData, $dayData);
            }
        }

        return json_encode($calendarData);
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
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\Phrase;
use App\Models\DailyAchievement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
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
        $selectedLanguage = Auth::user()->selected_language;
        Goal::truncate();
        GoalAchievement::truncate();
        
        $goal = new Goal();
        $goal->name = 'Reviews';
        $goal->user_id = Auth::user()->id;
        $goal->language = $selectedLanguage;
        $goal->type = 'review';
        $goal->quantity = 366;
        $goal->save();

        $achievement = new GoalAchievement();
        $achievement->language = $selectedLanguage;
        $achievement->user_id = Auth::user()->id;
        $achievement->goal_id = $goal->id;
        $achievement->achieved_quantity = 42;
        $achievement->goal_quantity = $goal->quantity;
        $achievement->day = Carbon::now()->subDays(1)->toDateString();
        $achievement->save();

        $achievement = new GoalAchievement();
        $achievement->language = $selectedLanguage;
        $achievement->user_id = Auth::user()->id;
        $achievement->goal_id = $goal->id;
        $achievement->achieved_quantity = 84;
        $achievement->goal_quantity = $goal->quantity + 14;
        $achievement->day = Carbon::now()->subDays(2)->toDateString();
        $achievement->save();

        $achievement = new GoalAchievement();
        $achievement->language = $selectedLanguage;
        $achievement->user_id = Auth::user()->id;
        $achievement->goal_id = $goal->id;
        $achievement->achieved_quantity = $goal->quantity - 24;
        $achievement->goal_quantity = $goal->quantity - 24;
        $achievement->day = Carbon::now()->subDays(3)->toDateString();
        $achievement->save();

        $goal = new Goal();
        $goal->name = 'Reading';
        $goal->user_id = Auth::user()->id;
        $goal->language = $selectedLanguage;
        $goal->type = 'read_words';
        $goal->quantity = 3000;
        $goal->save();

        $readingData = DailyAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        foreach ($readingData as $data) {
            if (!$data->read_words) {
                continue;
            }
            
            $achievement = new GoalAchievement();
            $achievement->language = $selectedLanguage;
            $achievement->user_id = Auth::user()->id;
            $achievement->goal_id = $goal->id;
            $achievement->achieved_quantity = $data->read_words;
            $achievement->goal_quantity = 3000;
            $achievement->day = $data->day;
            $achievement->save();
        }

        $goal = new Goal();
        $goal->name = 'New words';
        $goal->user_id = Auth::user()->id;
        $goal->language = $selectedLanguage;
        $goal->type = 'learn_words';
        $goal->quantity = 10;
        $goal->save();

        $achievement = new GoalAchievement();
        $achievement->user_id = Auth::user()->id;
        $achievement->language = $selectedLanguage;
        $achievement->goal_id = $goal->id;
        $achievement->achieved_quantity = 4;
        $achievement->goal_quantity = $goal->quantity;
        $achievement->day = Carbon::now()->subDays(1)->toDateString();
        $achievement->save();

        $achievement = new GoalAchievement();
        $achievement->user_id = Auth::user()->id;
        $achievement->language = $selectedLanguage;
        $achievement->goal_id = $goal->id;
        $achievement->achieved_quantity = 13;
        $achievement->goal_quantity = $goal->quantity;
        $achievement->day = Carbon::now()->subDays(2)->toDateString();
        $achievement->save();

        $achievement = new GoalAchievement();
        $achievement->user_id = Auth::user()->id;
        $achievement->language = $selectedLanguage;
        $achievement->goal_id = $goal->id;
        $achievement->achieved_quantity = 15;
        $achievement->goal_quantity = $goal->quantity;
        $achievement->day = Carbon::now()->subDays(3)->toDateString();
        $achievement->save();
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
        $languageStatistics->readWordCount = 0;
        $languageStatistics->readWordCountToday = 0;
        $languageStatistics->learned = EncounteredWord::select('id')->where('stage', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->learning = EncounteredWord::select('id')->where('stage', '<', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->learning_levels = EncounteredWord::select('stage')->where('stage', '<', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->sum('stage');
        $languageStatistics->days_of_learning = GoalAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->distinct('day')->count('day');
        $languageStatistics->words_to_review = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->inRandomOrder()->limit(50)->count('id');
        $languageStatistics->words_to_review_total = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->inRandomOrder()->limit(50)->count('id');

        if ($selectedLanguage == 'japanese') {
            // get unique kanji
            $uniqueKanji = [];
            $words = EncounteredWord::where('stage', 0)->where('language', 'Japanese')->where('user_id', Auth::user()->id)->get();
            foreach ($words as $word) {
                $kanji = preg_split("//u", $word->kanji, -1, PREG_SPLIT_NO_EMPTY);
                foreach($kanji as $currentKanji) {
                    if(!in_array($currentKanji, $uniqueKanji, true)) {
                        array_push($uniqueKanji, $currentKanji);
                    }
                }
            }
            $languageStatistics->kanjiCount = count($uniqueKanji);
        }
        
        return json_encode($languageStatistics);
    }

    public function getLanguage() {
        return Auth::user()->selected_language;
    }

    public function changeLanguage($language) {
        $user = Auth::user();
        $user->selected_language = $language;
        $user->save();
    }
}

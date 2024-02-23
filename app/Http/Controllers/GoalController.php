<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\EncounteredWord;

use App\Services\GoalService;

class GoalController extends Controller
{
    /*
        Returns a list of goals for the selected language. 
        It is used on the home page to display daily goals.
    */
    public function getGoals() {
        $selectedLanguage = Auth::user()->selected_language;
        $goals = Goal::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();

        foreach ($goals as $goal) {
            $goal->todaysQuantity = $goal->getTodaysQuantity();
        }

        return json_encode($goals);
    }

    /*
        Updates a goal's quantity.
    */
    public function updateGoal(Request $request) {
        $goalId = $request->post('goalId');
        $newGoalQuantity = $request->post('newGoalQuantity');

        $goal = Goal
            ::where('user_id', Auth::user()->id)
            ->where('id', $goalId)
            ->first();

        if (!$goal) {
            return;
        }

        $goal->quantity = $newGoalQuantity;
        $goal->save();

        // also update today's goal achievement
        $achievement = GoalAchievement
            ::where('user_id', Auth::user()->id)
            ->where('goal_id', $goal->id)
            ->where('day', Carbon::today()->format('Y-m-d'))
            ->first();

        if ($achievement) {
            $achievement->goal_quantity = $newGoalQuantity;
            $achievement->save();
        }
    }

    /*
        Returns GoalAchievements in a format that is used by calendar
        on the home page.
    */
    public function getCalendarData() {
        $selectedLanguage = Auth::user()->selected_language;
        $calendarData = [];

        // query goal achievements
        $goalAchievements = GoalAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        $goalAchievements = DB::table('goal_achievements')
            ->leftJoin('goals', 'goal_achievements.goal_id', '=', 'goals.id')
            ->select('goals.name', 'goals.type', 'goal_achievements.id', 'goal_achievements.day', 'goal_achievements.achieved_quantity', 'goal_achievements.goal_quantity')
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
            $achievementData = new \stdClass();
            $achievementData->id = $achievement->id;
            $achievementData->name = $achievement->name;
            $achievementData->type = $achievement->type;
            $achievementData->day = $achievement->day;
            $achievementData->achievedQuantity = $achievement->achieved_quantity;
            $achievementData->goalQuantity = $achievement->goal_quantity;
            
            if ($dayIndex !== -1) {
                array_push($calendarData[$dayIndex]->achievements, $achievementData);
            } else {
                $dayData = new \stdClass();
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
                $dayData = new \stdClass();
                $dayData->day = $review->day;
                $dayData->achievements = [];
                $dayData->reviewsDue = $review->quantity;
                array_push($calendarData, $dayData);
            }
        }

        return json_encode($calendarData);
    }

    /* 
        Updates a GoalAchievement based on Id, or creates one if it 
        doesn't exists yet for the given day and type.
    */
    public function updateCalendarData(Request $request) {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;
        $achievementGoalId = $request->post('achievementGoalId');
        $achievementType = $request->post('achievementType');
        $day = $request->post('day');
        $newValue = $request->post('newValue');
        
        if ($achievementGoalId === -1) {
            $goal = Goal::
            where('user_id', $userId)
            ->where('language', $language)
            ->where('type', $achievementType)
            ->first();
            
            if (!$goal) {
                return 'error';
            }
            
            $achievement = new GoalAchievement();
            $achievement->user_id = $userId;
            $achievement->language = $language;
            $achievement->goal_id = $goal->id;
            $achievement->achieved_quantity = $newValue;
            $achievement->goal_quantity = $goal->type == 'review' ? 1 : $goal->quantity;
            $achievement->day = $day;
            $achievement->save();
        } else {
            GoalAchievement::
                where('user_id', $userId)
                ->where('id', $achievementGoalId)
                ->update(['achieved_quantity' => $newValue]);
        }
        return 'success';
    }

    public function updateReviewGoalAchievement() {
        $userId = Auth::user()->id;
        $language = Auth::user()->selected_language;

        (new GoalService())->updateGoalAchievement($userId, $language, 'review', 1);
    }
}

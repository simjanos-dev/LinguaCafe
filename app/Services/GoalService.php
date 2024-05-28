<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\EncounteredWord;

class GoalService {
    
    public function __construct() {
    }
    
    public function createGoalsForLanguage($userId, $language) {
        $goal = Goal
            ::where('user_id', $userId)
            ->where('language', $language)
            ->first();

        if (!$goal) {
            $goal = new Goal();
            $goal->user_id = $userId;
            $goal->language = $language;
            $goal->name = 'Reviews';
            $goal->type = 'review';
            $goal->quantity = 0;
            $goal->save();

            $goal = new Goal();
            $goal->user_id = $userId;
            $goal->language = $language;
            $goal->name = 'Reading';
            $goal->type = 'read_words';
            $goal->quantity = 1000;
            $goal->save();

            $goal = new Goal();
            $goal->user_id = $userId;
            $goal->language = $language;
            $goal->name = 'New words';
            $goal->type = 'learn_words';
            $goal->quantity = 10;
            $goal->save();
        }

        return true;
    }

    /*
        Updates today's goal achievement, and if it
        does not exist yet, it will create one.
    */
    public function updateGoalAchievement($userId, $language, $type, $achievedQuantity) {
        $goal = Goal
            ::where('user_id', $userId)
            ->where('language', $language)
            ->where('type', $type)
            ->first();
        
        if (!$goal) {
            throw new \Exception('There was no goal found in the database with the given type, user id and language. This error should never occurr.');
        }

        $achievement = GoalAchievement
            ::where('user_id', $userId)
            ->where('language', $language)
            ->where('goal_id', $goal->id)
            ->where('day', Carbon::now()->toDateString())
            ->first();
        
        if (!$achievement) {
            $achievement = new GoalAchievement();
            $achievement->language = $language;
            $achievement->user_id = $userId;
            $achievement->goal_id = $goal->id;
            $achievement->achieved_quantity = 0;
            $achievement->goal_quantity = $goal->quantity;
            $achievement->day = Carbon::now()->toDateString();
        }
        

        $achievement->achieved_quantity += $achievedQuantity;
        $achievement->save();

        return true;
    }

    public function getGoals($userId, $language) {
        $goals = Goal
            ::where('user_id', $userId)
            ->where('language', $language)
            ->get();

        foreach ($goals as $goal) {
            $goal->todaysQuantity = $goal->getTodaysQuantity();
        }

        return $goals;
    }

    public function updateGoal($userId, $goalId, $newGoalQuantity) {
        $goal = Goal
            ::where('user_id', $userId)
            ->where('id', $goalId)
            ->first();

        if (!$goal) {
            throw new \Exception('Goal not found.');
        }

        $goal->quantity = $newGoalQuantity;
        $goal->save();

        // also update today's goal achievement
        $achievement = GoalAchievement
            ::where('user_id', $userId)
            ->where('goal_id', $goal->id)
            ->where('day', Carbon::today()->format('Y-m-d'))
            ->first();

        if ($achievement) {
            $achievement->goal_quantity = $newGoalQuantity;
            $achievement->save();
        }

        return true;
    }

    public function getCalendarData($userId, $language) {
        $calendarData = [];

        // query goal achievements
        $goalAchievements = GoalAchievement::where('user_id', $userId)->where('language', $language)->get();
        $goalAchievements = DB::table('goal_achievements')
            ->leftJoin('goals', 'goal_achievements.goal_id', '=', 'goals.id')
            ->select('goals.name', 'goals.type', 'goal_achievements.id', 'goal_achievements.day', 'goal_achievements.achieved_quantity', 'goal_achievements.goal_quantity')
            ->where('goals.user_id', $userId)
            ->where('goals.language', $language)->get();

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
        $reviewsDue = EncounteredWord::where('user_id', $userId)
            ->where('language', $language)
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

        return $calendarData;
    }

    public function updateCalendarData($userId, $language, $achievementGoalId, $achievementType, $day, $newValue) {
        if ($achievementGoalId === -1) {
            $goal = Goal::
                where('user_id', $userId)
                ->where('language', $language)
                ->where('type', $achievementType)
                ->first();
            
            if (!$goal) {
                throw new \Exception('Goal not found.');
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

        return true;
    }
}
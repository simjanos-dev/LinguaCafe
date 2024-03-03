<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

use App\Models\Goal;
use App\Models\GoalAchievement;

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
            // ->where('type', 'read_words')
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
}
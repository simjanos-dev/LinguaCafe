<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Goal;

class GoalService
{
    public function __construct()
    {
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
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GoalAchievement;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Goal extends Model
{
    use HasFactory;

    public function getTodaysQuantity() {
        $selectedLanguage = Auth::user()->selected_language;
        $achievement = GoalAchievement::where('user_id', Auth::user()->id)
            ->where('goal_id', $this->id)
            ->where('day', Carbon::now()->toDateString())
            ->first();

        if ($achievement) {
            return $achievement->achieved_quantity;
        } else {
            
            // if current goal is the default review goal,
            // then update the goal quantity, and create an
            // achievement, so this code does not run again today
            if ($this->type == 'review') {
                $this->quantity = $this->getTodaysReviewGoalQuantity();

                $achievement = new GoalAchievement();
                $achievement->language = $selectedLanguage;
                $achievement->user_id = Auth::user()->id;
                $achievement->goal_id = $this->id;
                $achievement->achieved_quantity = 0;
                $achievement->goal_quantity = $this->quantity;
                $achievement->day = Carbon::now()->toDateString();
                $achievement->save();
                $this->save();
            }

            return 0;
        }
    }

    public function getTodaysReviewGoalQuantity() {
        $selectedLanguage = Auth::user()->selected_language;
        $reviewWords = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0');
        $reviewPhrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0');
        $reviewWords = $reviewWords->where(function($query) {
            $query->whereDate('next_review', '<=', Carbon::today()->toDateString());
            $query->orWhere('relearning', true);
        });

        $reviewPhrases = $reviewPhrases->where(function($query) {
            $query->whereDate('next_review', '<=', Carbon::today()->toDateString());
            $query->orWhere('relearning', true);
        });

        return $reviewWords->count() + $reviewPhrases->count();
    }
}

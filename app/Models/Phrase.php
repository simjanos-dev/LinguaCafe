<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Goal;
use App\Models\GoalAchievement;

class Phrase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language',
        'words',
        'reading',
        'translation',
    ];

    public function setStage($stage) {
        $selectedLanguage = Auth::user()->selected_language;
        
        // if it's a newly saved phrase, update today's achievement
        if ($this->stage >= 0 && $stage < 0) {
            $goal = Goal::where('user_id', Auth::user()->id)
                ->where('language', $selectedLanguage)
                ->where('type', 'learn_words')
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
            

            $achievement->achieved_quantity++;
            $achievement->save();
        }

        if ($this->stage >= 0 && $stage < 0 && $stage !== -7) {
            $this->relearning = true;
        }

        if ($stage >= 0) {
            $this->relearning = false;
        }
        
        $this->stage = $stage;
        $reviewIntervals = config('langapp.reviewIntervals');

        // find the most optimal day for the next review
        if ($stage < 0) {
            $possibleDates = $reviewIntervals[$stage];
            $nextReviewIndex = 0;
            for ($i = 0; $i < count($possibleDates); $i++) {
                $data = new \stdClass();
                $data->date = Carbon::now()->addDays($possibleDates[$i])->toDateString();
                $data->count = Phrase::where('user_id', Auth::user()->id)->where('next_review', $data->date)->count();
                $possibleDates[$i] = $data;

                if ($possibleDates[$i]->count < $possibleDates[$nextReviewIndex]->count) {
                    $nextReviewIndex = $i;
                }
            }
            
            $this->next_review = $possibleDates[$nextReviewIndex]->date;
            if (is_null($this->added_to_srs)) {
                $this->added_to_srs = Carbon::now()->toDateString();
            }
        } else {
            $this->next_review = null;
        }
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EncounteredWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language',
        'stage',
        'word',
        'kanji',
        'reading',
        'translation',
        'example_sentence',
    ];

    public function setStage($stage) {
        if ($this->stage >= 0 && $stage < 0) {
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
                $data = new \StdClass();
                $data->date = Carbon::now()->addDays($possibleDates[$i])->toDateString();
                $data->count = EncounteredWord::where('user_id', Auth::user()->id)->where('next_review', $data->date)->count();
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

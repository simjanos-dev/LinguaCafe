<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;

class Course extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'name',
        'cover_image',
        'language',
    ];

    function getWordCounts($words) {
        $lessons = Lesson::where('user_id', Auth::user()->id)->where('course_id', $this->id)->get();
        $courseUniqueWordIds = [];
        
        foreach ($lessons as $lesson) {
            $uniqueWordIds = json_decode($lesson->unique_word_ids);
            
            foreach ($uniqueWordIds as $wordId) {
                if (!in_array($wordId, $courseUniqueWordIds, true)) {
                    array_push($courseUniqueWordIds, $wordId);
                }
            }
        }

        $wordCounts = new \StdClass();
        $wordCounts->total = $this->word_count;
        $wordCounts->unique = count($courseUniqueWordIds);
        $wordCounts->known = 0;
        $wordCounts->highlighted = 0;
        $wordCounts->new = 0;
        
        foreach($courseUniqueWordIds as $wordId) {
            if ($words[$wordId]['stage'] < 0) {
                $wordCounts->highlighted ++;
            }

            if ($words[$wordId]['stage'] == 0) {
                $wordCounts->known ++;
            }

            if ($words[$wordId]['stage'] == 2) {
                $wordCounts->new ++;
            }
        }

        return $wordCounts;
    }
}

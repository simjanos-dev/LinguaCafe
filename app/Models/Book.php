<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Chapter;
use Illuminate\Support\Facades\Auth;

class Book extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'name',
        'cover_image',
        'language',
    ];

    function getWordCounts($words) {
        $chapters = Chapter::where('user_id', Auth::user()->id)->where('book_id', $this->id)->get();
        $bookUniqueWordIds = [];
        
        foreach ($chapters as $chapter) {
            $uniqueWordIds = json_decode($chapter->unique_word_ids);
            
            foreach ($uniqueWordIds as $wordId) {
                if (!in_array($wordId, $bookUniqueWordIds, true)) {
                    array_push($bookUniqueWordIds, $wordId);
                }
            }
        }

        $wordCounts = new \stdClass();
        $wordCounts->total = $this->word_count;
        $wordCounts->unique = count($bookUniqueWordIds);
        $wordCounts->known = 0;
        $wordCounts->highlighted = 0;
        $wordCounts->new = 0;
        
        foreach($bookUniqueWordIds as $wordId) {
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

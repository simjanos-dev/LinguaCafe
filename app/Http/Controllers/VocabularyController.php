<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Phrase;
use App\Models\EncounteredWord;
use App\Models\DailyAchivement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VocabularyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function kanjiPrint() {
        $kanjiList = array_chunk(explode("\r\n", Storage::get('/public/kanji.txt')), 10);
        $kanjiList = array_slice($kanjiList, 0, 6);

        return view('kanji_print', [
            'kanjiList' => $kanjiList
        ]);
    }

    public function vocabularyPractice($mode = 'random', $lessonId = -1, $courseId = -1) {
        $selectedLanguage = Auth::user()->selected_language;
        $today = date('Y-m-d');

        if ($courseId !== -1) {
            $uniqueWords = [];
            $uniquePhraseIds = [];
            $lessons = Lesson::where('course_id', $courseId)->where('read_count', '>', 0)->where('user_id', Auth::user()->id)->get();
            foreach ($lessons as $lesson) {
                $words = json_decode(gzuncompress($lesson->processed_text));
                foreach ($words as $word) {
                    if (!in_array(mb_strtolower($word->word, 'UTF-8'), $uniqueWords, true)) {
                        array_push($uniqueWords, mb_strtolower($word->word, 'UTF-8'));
                    }
                    
                    foreach ($word->phraseIds as $phraseId) {
                        if (!in_array($phraseId, $uniquePhraseIds, true)) {
                            array_push($uniquePhraseIds, $phraseId);
                        }
                    }
                }
            }
            
            
            $words = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->where('last_level_up', '!=', $today)->whereIn('word', $uniqueWords)->inRandomOrder()->get();
            $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('last_level_up', '!=', $today)->whereIn('id', $uniquePhraseIds)->inRandomOrder()->get();
            if (count($words) == 0 && count($phrases) == 0) {
                $words = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->whereIn('word', $uniqueWords)->inRandomOrder()->get();
                $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->whereIn('id', $uniquePhraseIds)->inRandomOrder()->get();
            }
        } else if ($lessonId !== -1) {
            $lesson = Lesson::where('id', $lessonId)->where('user_id', Auth::user()->id)->first();            
            $words = json_decode(gzuncompress($lesson->processed_text));
            $uniqueWords = [];
            $uniquePhraseIds = [];

            foreach ($words as $word) {
                if (!in_array(mb_strtolower($word->word), $uniqueWords, true)) {
                    array_push($uniqueWords, mb_strtolower($word->word, 'UTF-8'));
                }

                foreach ($word->phraseIds as $phraseId) {
                    if (!in_array($phraseId, $uniquePhraseIds, true)) {
                        array_push($uniquePhraseIds, $phraseId);
                    }
                }
            }

            $words = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->where('last_level_up', '!=', $today)->whereIn('word', $uniqueWords)->inRandomOrder()->get();
            $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('last_level_up', '!=', $today)->whereIn('id', $uniquePhraseIds)->inRandomOrder()->get();
            if (count($words) == 0 && count($phrases) == 0) {
                $words = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->whereIn('word', $uniqueWords)->inRandomOrder()->get();
                $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->whereIn('id', $uniquePhraseIds)->inRandomOrder()->get();
            }
        } else if ($mode == 'random') {
            $words = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->where('last_level_up', '!=', $today)->inRandomOrder()->get();
            $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('last_level_up', '!=', $today)->inRandomOrder()->get();
            if (count($words) == 0 && count($phrases) == 0) {
                $words = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->inRandomOrder()->get();
                $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->inRandomOrder()->get();
            }
        } 

        $reviews = [];
        foreach ($words as $word) {
            $word->levelLocked = false;
            $word->type = 'word';
            array_push($reviews, $word);
        }

        foreach ($phrases as $phrase) {
            $phrase->levelLocked = false;
            $phrase->type = 'phrase';
            array_push($reviews, $phrase);
        }

        //echo('<pre>');var_dump($reviews );echo('</pre>');exit;
        return view('vocabulary_practice', [
            'reviews' => json_encode($reviews),
            'language' => $selectedLanguage
        ]);
    }

    public function finishVocabularyPractice(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $dailyAchivement = DailyAchivement::where('user_id', Auth::user()->id)->where('day', \date('Y-m-d'))->where('language', $selectedLanguage)->first();

        if (!$dailyAchivement) {
            $dailyAchivement = new DailyAchivement();
            $dailyAchivement->user_id = Auth::user()->id;
            $dailyAchivement->day = \date('Y-m-d');
            $dailyAchivement->read_words = 0;
            $dailyAchivement->reviewed_words = 0;
            $dailyAchivement->language = $selectedLanguage;
        }

        $dailyAchivement->read_words += $request->readWords;
        $dailyAchivement->reviewed_words += $request->reviewCount;
        $dailyAchivement->save();

        $changedReviews = json_decode($request->changedReviews);
        foreach ($changedReviews as $review) {
            if ($review->type == 'word') {
                $encounteredWord = EncounteredWord::where('user_id', Auth::user()->id)->where('id', $review->id)->first();
                if ($encounteredWord) {
                    $encounteredWord->stage = $review->stage;
                    $encounteredWord->last_level_up = $review->last_level_up;
                    $encounteredWord->save();
                }
            }

            if ($review->type == 'phrase') {
                $phrase = Phrase::where('user_id', Auth::user()->id)->where('id', $review->id)->first();
                if ($phrase) {
                    $phrase->stage = $review->stage;
                    $phrase->last_level_up = $review->last_level_up;
                    $phrase->save();
                }
            }
        }
    }
}

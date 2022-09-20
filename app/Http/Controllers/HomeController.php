<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EncounteredWord;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\DailyAchivement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;



class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dev() {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // language statistics
        $today = date('Y-m-d');
        $selectedLanguage = Auth::user()->selected_language;
        $languageStatistics = new \stdClass();
        $languageStatistics->readWordCount = DailyAchivement::select('read_words')->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->sum('read_words');
        $languageStatistics->readWordCountToday = DailyAchivement::select('read_words')->where('day', \date('Y-m-d'), Auth::user()->id)->where('language', $selectedLanguage)->sum('read_words');
        $languageStatistics->learned = EncounteredWord::select('id')->where('stage', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->learning = EncounteredWord::select('id')->where('stage', '<', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->learning_levels = EncounteredWord::select('stage')->where('stage', '<', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->sum('stage');
        $languageStatistics->days_of_learning = DailyAchivement::where('read_words', '>', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->words_to_review = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->where('last_level_up', '!=', $today)->inRandomOrder()->limit(50)->count('id');
        $languageStatistics->words_to_review_total = EncounteredWord::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('stage', '<', '0')->where('example_sentence', '!=', '')->inRandomOrder()->limit(50)->count('id');

        if ($selectedLanguage) {
            // get unique kanji
            $uniqueKanji = [];
            $words = EncounteredWord::where('stage', '<>', '2')->where('stage', '<>', '1')->where('language', 'Japanese')->where('user_id', Auth::user()->id)->get();
            foreach ($words as $word) {
                $kanji = preg_split("//u", $word->kanji, -1, PREG_SPLIT_NO_EMPTY);
                foreach($kanji as $currentKanji) {
                    if(!in_array($currentKanji, $uniqueKanji, true)) {
                        array_push($uniqueKanji, $currentKanji);
                    }
                }
            }
            $languageStatistics->kanjiCount = count($uniqueKanji);
        }
        
        return view('home', [
            'languageStatistics' => $languageStatistics
        ]);
    }

    public function changeLanguage($language) {
        $user = Auth::user();
        $user->selected_language = $language;
        $user->save();

        return redirect('/');
    }

    public function jishoRequest($keyword) {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, "https://jisho.org/api/v1/search/words?keyword=" . urlencode($keyword));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true); 
        $data = curl_exec($handle);
        return $data;
    }
}

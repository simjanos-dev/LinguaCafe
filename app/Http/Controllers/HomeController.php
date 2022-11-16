<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\Phrase;
use App\Models\DailyAchivement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;




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
        $fstart = microtime(true);
        $twords = 0;
        $devLessons = Lesson::where('language', 'japanese')->get();
        $index = 1;
        ob_implicit_flush(true);
        foreach ($devLessons as $devLesson) {
            $start = microtime(true);
            //if ($index > 10) break;
            $selectedLanguage = Auth::user()->selected_language;
            $kanjipattern = "/[a-zA-Z0-9０-９あ-んア-ンー。、:？！＜＞： 「」（）｛｝≪≫〈〉《》【】
                『』〔〕［］・\n\r\t\s\(\)　]/u";

            if (isset($devLesson->id)) {
                $lesson = Lesson::where('id', $devLesson->id)->where('user_id', Auth::user()->id)->first();
            } else {
                $lesson = new Lesson();
            }
            
            $lesson->user_id = Auth::user()->id;
            $lesson->name = $devLesson->name;
            $lesson->read_count = isset($devLesson->id) ? $lesson->read_count : 0;
            $lesson->word_count = 0;
            $lesson->book_id = $devLesson->book_id;
            $lesson->language = $selectedLanguage;
            $lesson->processed_text = '';
            $lesson->unique_words = '';

            $response = Http::post('127.0.0.1:8678/tokenizer/', [
                'raw_text' => preg_replace("/ {2,}/", " ", str_replace(["\r\n", "\r", "\n"], " NEWLINE ", $devLesson->raw_text)),
            ]);

            $lesson->processed_text = $response;

            $words = json_decode($response);
            $wordsToSkip = config('langapp.wordsToSkip');
            $wordCount = 0;
            $uniqueWords = [];
            $uniqueWordIds = [];

            $processedWords = [];
            $processedWordCount = 0;
            for ($i = 0; $i < count($words); $i++) {
                $word = new \StdClass();
                $word->word = $words[$i]->w;
                $word->reading = $words[$i]->r;
                $word->lemma = $words[$i]->l;
                $word->lemmaReading = $words[$i]->lr;
                $word->sentenceIndex = $words[$i]->si;

                if ($i < count($words) - 1 && $words[$i]->pos == 'VERB' && $words[$i + 1]->pos == 'VERB'  && !in_array($words[$i]->w, $wordsToSkip, true)) {
                    $i ++;
                    $word->word .= $words[$i]->w;
                    $word->reading .= $words[$i]->r;
                    $word->lemmaReading = $words[$i - 1]->r . $words[$i]->lr;
                    $word->lemma = $words[$i - 1]->w . $words[$i]->l;
                }
                
                if ($words[$i]->pos == 'VERB' && $words[$i]->w !== $words[$i]->l && $i < count($words) - 1 && $words[$i + 1]->pos == 'AUX') {
                    do {
                        $i ++;
                        if ($words[$i]->pos == 'AUX') {
                            $word->word .= $words[$i]->w;
                            $word->reading .= $words[$i]->r;
                        } else {
                            $i --; break;
                        }
                    } while($words[$i]->pos == 'AUX' && $i < count($words) - 1);
                } else if ($words[$i]->pos == 'VERB' && $words[$i]->w !== $words[$i]->l && $i < count($words) - 1 && $words[$i + 1]->pos == 'SCONJ') {
                    do {
                        $i ++;
                        if ($words[$i]->pos == 'SCONJ') {
                            $word->word .= $words[$i]->w;
                            $word->reading .= $words[$i]->r;
                        } else {
                            $i --; break;
                        }
                    } while($words[$i]->pos == 'SCONJ' && $i < count($words) - 1);
                }

                $word->phraseIds = [];
                
                if (!in_array($word->word, $wordsToSkip, true)) {
                    $wordCount ++;
                }
                
                $processedWords[$processedWordCount] = $word;
                $processedWordCount ++;

                if (!in_array(mb_strtolower($word->word), $uniqueWords, true)) {
                    array_push($uniqueWords, mb_strtolower($word->word, 'UTF-8'));
                    
                    $encounteredWord = EncounteredWord::select('id')->where('word', mb_strtolower($processedWords[$processedWordCount - 1]->word, 'UTF-8'))->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->first();
                    if (!$encounteredWord) {
                        if ($selectedLanguage == 'japanese') {
                            $kanji = preg_replace($kanjipattern, "", $processedWords[$processedWordCount - 1]->word);
                            $kanji = preg_split("//u", $kanji, -1, PREG_SPLIT_NO_EMPTY);
                        }

                        $encounteredWord = new EncounteredWord();
                        $encounteredWord->user_id = Auth::user()->id;
                        $encounteredWord->language = $selectedLanguage;
                        $encounteredWord->word = mb_strtolower($processedWords[$processedWordCount - 1]->word, 'UTF-8');
                        $encounteredWord->lemma = $processedWords[$processedWordCount - 1]->lemma;
                        $encounteredWord->kanji = $selectedLanguage == 'japanese' ? implode('', $kanji) : '';
                        $encounteredWord->reading = $processedWords[$processedWordCount - 1]->reading;
                        $encounteredWord->base_word = mb_strtolower($processedWords[$processedWordCount - 1]->lemma, 'UTF-8');
                        $encounteredWord->base_word_reading = $processedWords[$processedWordCount - 1]->lemmaReading;
                        $encounteredWord->example_sentence = '';
                        $encounteredWord->stage = 2;
                        $encounteredWord->translation = '';

                        if ($encounteredWord->base_word == $encounteredWord->word) {
                            $encounteredWord->base_word = '';
                            $encounteredWord->base_word_reading = '';
                        }

                        $encounteredWord->save();
                    }

                    array_push($uniqueWordIds, $encounteredWord->id);
                }
            }

            $lesson->word_count = $wordCount;
            $lesson->processed_text = gzcompress(json_encode($processedWords), 1);
            $lesson->unique_words = json_encode($uniqueWords);
            $lesson->unique_word_ids = json_encode($uniqueWordIds);
            $lesson->save();

            $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
            foreach($phrases as $phrase) {
                $phraseWords = json_decode($phrase->words);
                if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                    continue;
                }

                $lesson->updatePhraseIds($phrase->id);
                
            }
            
            $twords += count($processedWords);
            echo($index . ': ' . (microtime(true) - $start) . 's ' . count($processedWords) . 'words <br>');
            $index ++;
            echo str_pad('',4096);
            
        }

        echo('runtime: ' . (microtime(true) - $fstart) . 's ' . $twords . 'words <br>');
        ob_implicit_flush(false);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function statistics()
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

        if ($selectedLanguage == 'japanese') {
            // get unique kanji
            $uniqueKanji = [];
            $words = EncounteredWord::where('stage', 0)->where('language', 'Japanese')->where('user_id', Auth::user()->id)->get();
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
        
        return json_encode($languageStatistics);
    }

    public function changeLanguage($language) {
        $user = Auth::user();
        $user->selected_language = $language;
        $user->save();

        return redirect('/');
    }
}

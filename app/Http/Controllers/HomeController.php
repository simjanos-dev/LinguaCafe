<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ExampleSentence;
use App\Models\EncounteredWord;
use App\Models\Goal;
use App\Models\GoalAchievement;
use App\Models\Phrase;
use App\Models\Lesson;
use App\Models\TextBlock;
use App\Services\GoalService;

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
        // $lessons = Lesson::get();
        // $lessonCount = count($lessons);
        // foreach ($lessons as $lessonIndex => $lesson) {
        //     $textBlock = new TextBlock();
        //     $textBlock->rawText = $lesson->raw_text;
        //     $textBlock->tokenizeRawText();
        //     $textBlock->processTokenizedWords();
        //     $textBlock->collectUniqueWords();
        //     $textBlock->updateAllPhraseIds();
        //     $textBlock->createNewEncounteredWords();
    
        //     $uniqueWordIds = DB
        //         ::table('encountered_words')
        //         ->select('id')
        //         ->where('user_id', $lesson->user_id)
        //         ->where('language', $lesson->language)
        //         ->whereIn('word', $textBlock->uniqueWords)
        //         ->pluck('id')
        //         ->toArray();
                
        //     // update lesson word data
        //     $lesson->word_count = $textBlock->getWordCount();
        //     $lesson->unique_words = json_encode($textBlock->uniqueWords);
        //     $lesson->unique_word_ids = json_encode($uniqueWordIds);
        //     $lesson->setProcessedText($textBlock->processedWords);
        //     $lesson->save();

        //     echo(($lessonIndex + 1) . '/' . $lessonCount . ' finished <br>');
        //     echo str_repeat(' ',1024*64);
        //     flush();
        // }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $selectedLanguage = Auth::user()->selected_language;
        $userCount = User::count();
        $theme = $_COOKIE['theme'] ?? 'light';
        
        return view('home', [
            'language' => $selectedLanguage,
            'userCount' => $userCount,
            'theme' => $theme
        ]);
    }

    public function getStatistics() {
        // language statistics
        $today = date('Y-m-d');
        $selectedLanguage = Auth::user()->selected_language;
        $languageStatistics = new \stdClass();

        $readingGoal = Goal::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->where('type', 'read_words')
            ->first();

        $languageStatistics->days = new \stdClass();
        $languageStatistics->days->name = 'Days of activity';
        $languageStatistics->days->value = GoalAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('achieved_quantity', '<>', 0)->distinct('day')->count('day');
        $languageStatistics->days->color = 'statisticsDays';
        $languageStatistics->days->icon = 'mdi-calendar-check';

        $languageStatistics->readWordCount = new \stdClass();
        $languageStatistics->readWordCount->name = 'Read words';
        $languageStatistics->readWordCount->value = GoalAchievement::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('goal_id', $readingGoal->id)->sum('achieved_quantity');
        $languageStatistics->readWordCount->color = 'statisticsReadWords';
        $languageStatistics->readWordCount->icon = 'mdi-book-open-variant';

        if ($selectedLanguage == 'japanese') {
            // get unique kanji
            $uniqueKanji = [];
            $words = EncounteredWord::where('stage', '<=', 0)->where('language', 'japanese')->where('user_id', Auth::user()->id)->get();
            foreach ($words as $word) {
                $kanji = preg_split("//u", $word->kanji, -1, PREG_SPLIT_NO_EMPTY);
                foreach($kanji as $currentKanji) {
                    if(!in_array($currentKanji, $uniqueKanji, true)) {
                        array_push($uniqueKanji, $currentKanji);
                    }
                }
            }
            
            $languageStatistics->kanji = new \stdClass();
            $languageStatistics->kanji->name = 'Kanji';
            $languageStatistics->kanji->value = count($uniqueKanji);
            $languageStatistics->kanji->color = 'statisticsKanji';
            $languageStatistics->kanji->icon = 'mdi-ideogram-cjk';
        }
        
        $languageStatistics->known = new \stdClass();
        $languageStatistics->known->name = 'Known words';
        $languageStatistics->known->value = EncounteredWord::select('id')->where('stage', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->known->color = 'statisticsKnownWords';
        $languageStatistics->known->icon = 'mdi-credit-card-check';

        $languageStatistics->learning = new \stdClass();
        $languageStatistics->learning->name = 'Words currently studied';
        $languageStatistics->learning->value = EncounteredWord::select('id')->where('stage', '<', 0)->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->count('id');
        $languageStatistics->learning->color = 'statisticsLearningWords';
        $languageStatistics->learning->icon = 'mdi-school';
        
        return json_encode($languageStatistics);
    }

    public function getLanguage() {
        return Auth::user()->selected_language;
    }

    public function changeLanguage($language) {
        $user = Auth::user();
        $user->selected_language = strtolower($language);
        $user->save();

        (new GoalService())->createGoalsForLanguage($user->id, $language);
    }

    public function getConfig($configPath) {
        $config = config($configPath);

        return json_encode($config);
    }
}

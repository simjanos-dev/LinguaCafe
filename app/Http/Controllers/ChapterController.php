<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\LessonWord;
use App\Models\Phrase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Goal;
use App\Models\GoalAchievement;

class ChapterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getChapters(Request $request) {
        $bookId = intval($request->bookId);
        $book = Book::where('id', $bookId)->where('user_id', Auth::user()->id)->first();
        $chapters = Lesson::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])->where('book_id', $bookId)->where('user_id', Auth::user()->id)->get();
        $words = EncounteredWord::select(['id', 'word', 'stage'])->where('user_id', Auth::user()->id)->where('language', Auth::user()->selected_language)->get()->keyBy('id')->toArray();
        $book->wordCount = $book->getWordCounts($words);

        for ($i = 0; $i < count($chapters); $i++) {
            $chapters[$i]->wordCount = $chapters[$i]->getWordCounts($words);
        }
        
        $data = new \StdClass();
        $data->book = $book;
        $data->chapters = $chapters;

        return json_encode($data);
    }

    public function getChapterForReader(Request $request) 
    {        
        $lessonId = $request->chapterId;
        $wordsToSkip = config('langapp.wordsToSkip');
        $selectedLanguage = Auth::user()->selected_language;
        

        $lesson = Lesson::where('id', $lessonId)->where('user_id', Auth::user()->id)->first();
        $uniqueWords = json_decode($lesson->unique_words);
        $book = Book::where('id', $lesson->book_id)->where('user_id', Auth::user()->id)->first();
        $lessons = Lesson::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])->where('book_id', $book->id)->where('user_id', Auth::user()->id)->get();
        $encounteredWords = DB::table('encountered_words')->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereIn('word', $uniqueWords)->get();
        $words = LessonWord::where('user_id', Auth::user()->id)->where('lesson_id', $lessonId)->get()->toArray();

        // get lesson word counts
        $uniqueWordsForWordCounts = EncounteredWord
            ::select(['id', 'word', 'stage'])
            ->where('user_id', Auth::user()->id)
            ->where('language', Auth::user()
            ->selected_language)
            ->get()
            ->keyBy('id')
            ->toArray();

        for ($i = 0; $i < count($lessons); $i++) {
            $lessons[$i]->wordCount = $lessons[$i]->getWordCounts($uniqueWordsForWordCounts);
        }

        // get unique phrase ids
        $phraseIds = [];
        for ($wordCounter = 0; $wordCounter < count($words); $wordCounter ++) {
            $words[$wordCounter]['phrase_ids'] = json_decode($words[$wordCounter]['phrase_ids']);
            for ($phraseCounter = 0; $phraseCounter < count($words[$wordCounter]['phrase_ids']); $phraseCounter ++) {
                if (!in_array($words[$wordCounter]['phrase_ids'][$phraseCounter], $phraseIds)) {
                    array_push($phraseIds, $words[$wordCounter]['phrase_ids'][$phraseCounter]);
                }
            }
        }

        sort($phraseIds);
        
        // get unique words from lesson
        for ($wordCounter = 0; $wordCounter < count($words); $wordCounter ++) {
            // make the word into an object
            $word = $words[$wordCounter];
            $word['selected'] = false;
            $word['hover'] = false;
            $word['phraseStage'] = 'learning';
            $word['phraseStart'] = false;
            $word['phraseEnd'] = false;
            $word['phraseIndexes'] = [];

            // replace phrase ids with phrase indexes
            foreach($word['phrase_ids'] as $phraseIndex => $phraseId) {
                $index = array_search($phraseId, $phraseIds);
                array_push($word['phraseIndexes'], $index);
            }

            $wordId = $encounteredWords->search(function ($item, $key) use($word) {
                return $item->word == mb_strtolower($word['word']);
            });

            if ($wordId !== false) {
                $word['stage'] = $encounteredWords[$wordId]->stage;
                $word['lookup_count'] = $encounteredWords[$wordId]->lookup_count;
                $encounteredWords[$wordId]->read_count ++;
            }

            $words[$wordCounter] = $word;
        }

        $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereIn('id', $phraseIds)->orderBy('id')->get();
        for ($i = 0; $i < count($phrases); $i++) {
            $phrases[$i]->words = json_decode($phrases[$i]->words);
        }

        $data = new \StdClass();
        $data->words = $words;
        $data->uniqueWords = $encounteredWords;
        $data->phrases = $phrases;
        $data->bookName = $book->name;
        $data->lessonId = $lesson->id;
        $data->lessonName = $lesson->name;
        $data->bookId = $book->id;
        $data->language = $lesson->language;
        $data->lessons = $lessons;
        $data->wordCount = $lesson->word_count;
        
        return json_encode($data);
    }

    public function finishChapter(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $uniqueWords = json_decode($request->uniqueWords);
        $phrases = json_decode($request->phrases);
        $deletedPhrases = json_decode($request->deletedPhrases);
        $autoMoveWordsToKnown = boolval($request->autoMoveWordsToKnown);
        $today = date('Y-m-d');
        
        // automove words that the user sees the first time in the software,
        // but they already know it to learned stage.
        DB::beginTransaction();
        if ($autoMoveWordsToKnown) {
            foreach ($uniqueWords as $uniqueWordData) {
                $saveData = [];
                $saveData['read_count'] = $uniqueWordData->read_count;
                
                if ($uniqueWordData->stage == 2) {
                    $saveData['stage'] = 0;
                    $saveData['added_to_srs'] = null;
                    $saveData['next_review'] = null;
                    $saveData['relearning'] = false;
                }

                EncounteredWord::where('id', $uniqueWordData->id)->update($saveData);
            }
        }

        DB::commit();

        // increase lesson read count
        $lesson = Lesson::where('id', $request->lessonId)->where('user_id', Auth::user()->id)->first();
        $lesson->read_count ++;
        $lesson->save();

        // updage today's reading achievement
        $goal = Goal::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->where('type', 'read_words')
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
        

        $achievement->achieved_quantity += $lesson->word_count;
        $achievement->save();
        
        return 'success';
    }

    public function getChapterForEdit(Request $request) {
        $chapter = Lesson::select(['name', 'raw_text'])->where('id', $request->chapterId)->where('user_id', Auth::user()->id)->first();
        $chapter->raw_text = str_replace(" NEWLINE \r\n", "\r\n", $chapter->raw_text);
        return $chapter;
    }

    public function saveChapter(Request $request) {
        \DB::disableQueryLog();
        $selectedLanguage = Auth::user()->selected_language;
        $kanjipattern = "/[a-zA-Z0-9０-９あ-んア-ンー。、:？！＜＞： 「」（）｛｝≪≫〈〉《》【】
            『』〔〕［］・\n\r\t\s\(\)　]/u";

        // retrieve lesson
        if (isset($request->lesson_id)) {
            $lesson = Lesson::where('id', $request->lesson_id)->where('user_id', Auth::user()->id)->first();
        } else {
            $lesson = new Lesson();
        }
        
        // set lesson data from post data
        $lesson->user_id = Auth::user()->id;
        $lesson->name = $request->name;
        $lesson->read_count = isset($request->lesson_id) ? $lesson->read_count : 0;
        $lesson->word_count = 0;
        $lesson->book_id = $request->book;
        $lesson->language = $selectedLanguage;
        $lesson->raw_text = $request->raw_text;
        $lesson->unique_words = '';
        
        // tokenizing raw text
        $response = Http::post('langapp-python-service-dev:8678/tokenizer/', [
            'raw_text' => preg_replace("/ {2,}/", " ", str_replace(["\r\n", "\r", "\n"], " NEWLINE ", $request->raw_text)),
        ]);

        $words = json_decode($response);


        // processing words
        $wordsToSkip = config('langapp.wordsToSkip');
        $notSkippedWordCount = 0;
        $uniqueWords = [];
        $uniqueWordIds = [];
        $processedWords = [];
        $processedWordCount = 0;
        $wordCount = count($words);

        for ($i = 0; $i < $wordCount; $i++) {
            $word = new \stdClass();
            $word->user_id = Auth::user()->id;
            $word->lesson_id = $lesson->id;
            $word->word_index = $i;
            $word->sentence_index = $words[$i]->si;
            $word->word = $words[$i]->w;
            $word->reading = $words[$i]->r;
            $word->lemma = $words[$i]->l;
            $word->lemma_reading = $words[$i]->lr;
            $word->pos = $words[$i]->pos;
            $word->phrase_ids = [];

            if ($i < $wordCount - 1 && $words[$i]->pos == 'VERB' && $words[$i + 1]->pos == 'VERB') {
                $i ++;
                $word->word .= $words[$i]->w;
                $word->reading .= $words[$i]->r;
                $word->lemma_reading = $words[$i - 1]->r . $words[$i]->lr;
                $word->lemma = $words[$i - 1]->w . $words[$i]->l;
            }
            
            if ($words[$i]->pos == 'VERB' && $words[$i]->w !== $words[$i]->l && $i < $wordCount - 1 && $words[$i + 1]->pos == 'AUX') {
                do {
                    $i ++;
                    if ($words[$i]->pos == 'AUX') {
                        $word->word .= $words[$i]->w;
                        $word->reading .= $words[$i]->r;
                    } else {
                        $i --; break;
                    }
                } while($words[$i]->pos == 'AUX' && $i < $wordCount - 1);
            } else if ($words[$i]->pos == 'VERB' && $words[$i]->w !== $words[$i]->l && $i < $wordCount - 1 && $words[$i + 1]->pos == 'SCONJ') {
                do {
                    $i ++;
                    if ($words[$i]->pos == 'SCONJ') {
                        $word->word .= $words[$i]->w;
                        $word->reading .= $words[$i]->r;
                    } else {
                        $i --; break;
                    }
                } while($words[$i]->pos == 'SCONJ' && $i < $wordCount - 1);
            }
            
            if (!in_array($word->word, $wordsToSkip, true)) {
                $notSkippedWordCount ++;
            }
            
            $processedWords[$processedWordCount] = $word;
            $processedWordCount ++;

            // collect unique words, and save words which the user
            // sees for the first time.
            if (!in_array(mb_strtolower($word->word), $uniqueWords, true)) {
                array_push($uniqueWords, mb_strtolower($word->word, 'UTF-8'));

                // retrieve unique word from database
                $encounteredWord = EncounteredWord
                    ::select('id')
                    ->where('word', mb_strtolower($processedWords[$processedWordCount - 1]->word, 'UTF-8'))
                    ->where('user_id', Auth::user()->id)
                    ->where('language', $selectedLanguage)
                    ->first();

                // check if the word exists in the database already,
                // otherwise save it.
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
                    $encounteredWord->base_word = $processedWords[$processedWordCount - 1]->lemma;
                    $encounteredWord->kanji = $selectedLanguage == 'japanese' ? implode('', $kanji) : '';
                    $encounteredWord->reading = $processedWords[$processedWordCount - 1]->reading;
                    $encounteredWord->base_word_reading = $processedWords[$processedWordCount - 1]->lemma_reading;
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

        // update lesson word data
        $lesson->word_count = $notSkippedWordCount;
        $lesson->unique_words = json_encode($uniqueWords);
        $lesson->unique_word_ids = json_encode($uniqueWordIds);
        $lesson->save();

        // update phrase ids
        // phrase ids mark which phrases contain a certain word
        $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        foreach($phrases as $phrase) {
            // decode phrase words array
            $phraseWords = array_unique(json_decode($phrase->words));

            // check if the lesson contains the phrase
            // otherwise skip the algorithm. 
            $containesPhrase = true;
            foreach ($phraseWords as $phraseWord) {
                if (!in_array($phraseWord, $uniqueWords, true)) {
                    $containesPhrase = false;
                    break;
                }
            }

            if (!$containesPhrase) {
                continue;
            }

            // update phrase ids of the lesson
            $lesson->updatePhraseIds($phrase->id, $processedWords);
        }

        // save lesson words
        DB::beginTransaction();
        DB::delete('DELETE FROM lesson_words WHERE user_id = ? AND lesson_id = ?', [Auth::user()->id, $lesson->id]);
        foreach ($processedWords as $word) {
            $word->phrase_ids = json_encode($word->phrase_ids);
            DB::insert('
                INSERT INTO lesson_words 
                    (user_id, lesson_id, word_index, sentence_index, word, reading, lemma, lemma_reading, pos, phrase_ids) 
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',[
                $word->user_id, $word->lesson_id, $word->word_index, $word->sentence_index, $word->word,
                $word->reading, $word->lemma, $word->lemma_reading, $word->pos, $word->phrase_ids]);
        }

        DB::commit();

        // update book word count
        $bookWordCount = intval(Lesson::where('user_id', Auth::user()->id)->where('book_id', $lesson->book_id)->sum('word_count'));
        Book::where('user_id', Auth::user()->id)->where('id', $lesson->book_id)->update(['word_count' => $bookWordCount]);

        return 'success';
    }
}
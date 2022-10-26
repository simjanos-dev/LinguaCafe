<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EncounteredWord;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\Phrase;
use App\Models\DailyAchivement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
        $wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
            '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
            '«', '»', "'", '’', '–', 'NEWLINE'];
        $selectedLanguage = Auth::user()->selected_language;
        

        $lesson = Lesson::where('id', $lessonId)->where('user_id', Auth::user()->id)->first();
        $uniqueWords = json_decode($lesson->unique_words);
        $book = Book::where('id', $lesson->book_id)->where('user_id', Auth::user()->id)->first();
        $lessons = Lesson::select(['id', 'name', 'read_count', 'word_count', 'unique_word_ids'])->where('book_id', $book->id)->where('user_id', Auth::user()->id)->get();
        $encounteredWords = DB::table('encountered_words')->select(DB::raw('*, false as checked, 0 as appearance_in_text'))->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereIn('word', $uniqueWords)->get();
        $words = json_decode(gzuncompress($lesson->processed_text));

        // get lesson word counts
        $uniqueWordsForWordCounts = EncounteredWord::select(['id', 'word', 'stage'])->where('user_id', Auth::user()->id)->where('language', Auth::user()->selected_language)->get()->keyBy('id')->toArray();
        for ($i = 0; $i < count($lessons); $i++) {
            $lessons[$i]->wordCount = $lessons[$i]->getWordCounts($uniqueWordsForWordCounts);
        }

        // get unique phrase ids
        $phraseIds = [];
        for ($wordCounter = 0; $wordCounter < count($words); $wordCounter ++) {        
            for ($phraseCounter = 0; $phraseCounter < count($words[$wordCounter]->phraseIds); $phraseCounter ++) {
                if (!in_array($words[$wordCounter]->phraseIds[$phraseCounter], $phraseIds)) {
                    array_push($phraseIds, $words[$wordCounter]->phraseIds[$phraseCounter]);
                }
            }
        }

        sort($phraseIds);
        // get unique words from lesson
        for ($wordCounter = 0; $wordCounter < count($words); $wordCounter ++) {
            // make the word into an object
            $word = $words[$wordCounter];
            $word->selected = false;
            $word->hover = false;
            $word->phraseStage = 'learning';
            $word->phraseStart = false;
            $word->phraseEnd = false;
            $word->phraseIndexes = [];

            // replace phrase ids with phrase indexes
            foreach($word->phraseIds as $phraseIndex => $phraseId) {
                $index = array_search($phraseId, $phraseIds);
                array_push($word->phraseIndexes, $index);
            }

            $wordId = $encounteredWords->search(function ($item, $key) use($word) {
                return $item->word == mb_strtolower($word->word);
            });

            if ($wordId !== false) {
                $word->stage = $encounteredWords[$wordId]->stage;
                $word->lookup_count = $encounteredWords[$wordId]->lookup_count;
                $word->last_level_up = $encounteredWords[$wordId]->last_level_up;
                $encounteredWords[$wordId]->read_count ++;
            }

            $words[$wordCounter] = $word;
        }

        $lesson->processed_text = json_encode($words);
        $phrases = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->whereIn('id', $phraseIds)->orderBy('id')->get();
        for ($i = 0; $i < count($phrases); $i++) {
            $phrases[$i]->words = json_decode($phrases[$i]->words);
            $phrases[$i]->checked = false;
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
        
        // update words
        DB::beginTransaction();
        foreach ($uniqueWords as $uniqueWordData) {
            $stage = $uniqueWordData->stage;
            $last_level_up = $uniqueWordData->stage;

            // increase word stage strength if it wasn't checked
            if ($uniqueWordData->stage < 0 && !$uniqueWordData->checked && 
                $uniqueWordData->last_level_up !== $today) {
                $stage++;
                $last_level_up = $today;
            }

            // these are words that the user sees the first time in the software,
            // but they already know it
            if ($autoMoveWordsToKnown && $uniqueWordData->stage == 2) {
                $stage = 0;
                $last_level_up = $today;
            }

            EncounteredWord::where('id', $uniqueWordData->id)->update([
                'translation' => $uniqueWordData->translation,
                'reading' => $uniqueWordData->reading,
                'base_word' => $uniqueWordData->base_word,
                'base_word_reading' => $uniqueWordData->base_word_reading,
                'example_sentence' => $uniqueWordData->example_sentence,
                'lookup_count' => $uniqueWordData->lookup_count,
                'read_count' => $uniqueWordData->read_count,
                'last_level_up' => $last_level_up,
                'stage' => $stage
            ]);
        }

        DB::commit();

        // increase lesson read count
        $lesson = Lesson::where('id', $request->lessonId)->where('user_id', Auth::user()->id)->first();
        $lesson->read_count ++;
        $lesson->save();

        // save phrases
        foreach ($phrases as $currentPhrase) {
            $newPhrase = false;
            if ($currentPhrase->id == -1) {
                $newPhrase = true;
                $phrase = new Phrase();
                $phrase->user_id = Auth::user()->id;
                $phrase->language = $selectedLanguage;
            } else {
                $phrase = Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('id', $currentPhrase->id)->first();
            }

            $phrase->words = json_encode($currentPhrase->words);
            $phrase->stage = intval($currentPhrase->stage);
            $phrase->reading = $currentPhrase->reading;
            $phrase->translation = $currentPhrase->translation;
            if ($phrase->stage < 0 && !$currentPhrase->checked && 
                $phrase->last_level_up !== $today) {
                $phrase->stage++;
                $phrase->last_level_up = $today;
            }

            $phrase->save();

            // if this phrase was not in the database before, 
            // mark the phrase in all text
            if ($newPhrase) {
                $lessons = Lesson::all();
                foreach ($lessons as $currentLesson) {
                    $phraseWords = array_unique($currentPhrase->words);
                    $uniqueWords = json_decode($currentLesson->unique_words);
                    if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                        continue;
                    }

                    $currentLesson->updatePhraseIds($phrase->id);
                }
            }
        }

        // delete phrases
        foreach ($deletedPhrases as $currentPhrase) {
            $lessons = Lesson::all();
            foreach ($lessons as $currentLesson) {
                $currentLesson->deletePhraseIds($currentPhrase->id);
            }
            
            Phrase::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('id', $currentPhrase->id)->delete();
        }

        // increase read word count
        $dailyAchivement = DailyAchivement::where('user_id', Auth::user()->id)->where('day', \date('Y-m-d'))->where('language', $selectedLanguage)->first();
        if (!$dailyAchivement) {
            $dailyAchivement = new DailyAchivement();
            $dailyAchivement->user_id = Auth::user()->id;
            $dailyAchivement->day = \date('Y-m-d');
            $dailyAchivement->read_words = 0;
            $dailyAchivement->reviewed_words = 0;
            $dailyAchivement->language = $request->language;
        }

        $dailyAchivement->read_words += $lesson->word_count;
        $dailyAchivement->save();

        return 'success';
    }

    public function getChapterForEdit(Request $request) {
        $chapter = Lesson::select(['name', 'raw_text'])->where('id', $request->chapterId)->where('user_id', Auth::user()->id)->first();
        $chapter->raw_text = str_replace(" NEWLINE \r\n", "\r\n", $chapter->raw_text);
        return $chapter;
    }

    public function saveChapter(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $kanjipattern = "/[a-zA-Z0-9０-９あ-んア-ンー。、:？！＜＞： 「」（）｛｝≪≫〈〉《》【】
            『』〔〕［］・\n\r\t\s\(\)　]/u";

        if (isset($request->lesson_id)) {
            $lesson = Lesson::where('id', $request->lesson_id)->where('user_id', Auth::user()->id)->first();
        } else {
            $lesson = new Lesson();
        }
        
        $lesson->user_id = Auth::user()->id;
        $lesson->name = $request->name;
        $lesson->read_count = isset($request->lesson_id) ? $lesson->read_count : 0;
        $lesson->word_count = 0;
        $lesson->book_id = $request->book;
        $lesson->language = $selectedLanguage;
        $lesson->raw_text = $request->raw_text;
        $lesson->processed_text = '';
        $lesson->unique_words = '';
        
        $response = Http::post('127.0.0.1:8678/tokenizer/', [
            'raw_text' => str_replace(["\r\n", "\r", "\n"], " NEWLINE \r\n", $request->raw_text),
        ]);

        $lesson->processed_text = json_decode($response);
        $words = json_decode($response);

        $wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                        '«', '»', "'", '’', '–', 'NEWLINE'];
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


            // if ($i < count($words) - 1 && $words[$i]->pos == 'VERB' && $words[$i + 1]->pos == 'VERB') {
            //     $i ++;
            //     $word->word .= $words[$i]->w;
            //     $word->reading .= $words[$i]->r;
            //     $word->lemma = $words[$i - 1]->w . $words[$i]->l;
            // }
            
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
                    $encounteredWord->base_word = $processedWords[$processedWordCount - 1]->lemma;
                    $encounteredWord->kanji = $selectedLanguage == 'japanese' ? implode('', $kanji) : '';
                    $encounteredWord->reading = $processedWords[$processedWordCount - 1]->reading;
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
            $phraseWords = array_unique(json_decode($phrase->words));
            if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                continue;
            }

            $lesson->updatePhraseIds($phrase->id);
        }

        // update book word count
        $bookWordCount = intval(Lesson::where('user_id', Auth::user()->id)->where('book_id', $lesson->book_id)->sum('word_count'));
        Book::where('user_id', Auth::user()->id)->where('id', $lesson->book_id)->update(['word_count' => $bookWordCount]);

        return 'success';
    }
}
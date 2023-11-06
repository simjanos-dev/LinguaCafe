<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use \Exception;
use Carbon\Carbon;
use App\Models\TextBlock;
use App\Models\EncounteredWord;
use App\Models\Phrase;
use App\Models\Kanji;
use App\Models\Radical;
use App\Models\Book;
use App\Models\Lesson;
use App\Models\LessonWord;
use App\Models\ExampleSentence;
use App\Models\Goal;
use App\Models\GoalAchievement;

class VocabularyController extends Controller
{
    private $limit = 30;

    public function getWord($wordId) {
        $word = EncounteredWord
            ::where('user_id', Auth::user()->id)
            ->where('id', $wordId)
            ->first();
        
        return json_encode($word);
    }

    public function saveWord(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $word = EncounteredWord::where('user_id', Auth::user()->id)->where('id', $request->id)->first();
        
        // if the reviewed item got leveled up or relearning state got removed
        // while being reviewed, then increase the daily review goal
        if (!is_null($request->changedWhileReviewing) && $request->changedWhileReviewing) {
            if (($request->stage <= 0 && $request->stage > $word->stage) || 
                (isset($request->relearning) && $request->relearning === false && boolval($word->relearning))) {
                
                $goal = Goal::where('user_id', Auth::user()->id)
                    ->where('language', $selectedLanguage)
                    ->where('type', 'review')
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
        
                $achievement->achieved_quantity ++;
                $achievement->save();
            }
        }

        if (isset($request->translation)) {
            $word->translation = $request->translation;
        }

        if (isset($request->reading)) {
            $word->reading = $request->reading;
        }

        if (isset($request->base_word)) {
            $word->base_word = $request->base_word;
        }

        if (isset($request->base_word_reading)) {
            $word->base_word_reading = $request->base_word_reading;
        }

        if (isset($request->lookup_count)) {
            $word->lookup_count = $request->lookup_count;
        }

        if (isset($request->read_count)) {
            $word->read_count = $request->read_count;
        }

        if (isset($request->stage)) {
            $word->setStage($request->stage);
        }

        if (isset($request->relearning)) {
            $word->relearning = boolval($request->relearning);
        }
        
        $word->save();

        return $request->id . ' vocabulary updated<br>';
    }

    public function getPhrase($phraseId) {
        $phrase = Phrase
            ::where('user_id', Auth::user()->id)
            ->where('id', $phraseId)
            ->first();
        
        return json_encode($phrase);
    }

    public function savePhrase(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $isNewPhrase = false;


        if (is_null($request->id)) {
            $phrase = new Phrase();
            $phrase->reading = '';
            $isNewPhrase = true;

            // it's required here because empty 
            // post parameter string passes as null
            $phrase->translation = '';
        } else {
            $phrase = Phrase::where('user_id', Auth::user()->id)->where('id', $request->id)->first();
        }

        // if the reviewed item got leveled up or relearning state got removed
        // while being reviewed, then increase the daily review goal
        if (!is_null($request->changedWhileReviewing) && $request->changedWhileReviewing) {
            if (($request->stage <= 0 && $request->stage > $phrase->stage) || 
                (isset($request->relearning) && $request->relearning === false && boolval($phrase->relearning))) {
                
                $goal = Goal::where('user_id', Auth::user()->id)
                    ->where('language', $selectedLanguage)
                    ->where('type', 'review')
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
        
                $achievement->achieved_quantity ++;
                $achievement->save();
            }
        }

        $phrase->user_id = Auth::user()->id;
        $phrase->language = $selectedLanguage;
        if (isset($request->words)) {
            $phrase->words = json_encode($request->words);
            $phrase->words_searchable = implode('', $request->words);
        }

        if (isset($request->reading)) {
            $phrase->reading = $request->reading;
        }

        if (isset($request->translation)) {
            $phrase->translation = $request->translation;
        }

        if (isset($request->stage)) {
            $phrase->setStage($request->stage);
        }

        if (isset($request->relearning)) {
            $phrase->relearning = boolval($request->relearning);
        }
        
        $phrase->save();

        // update phrase ids in lesson texts
        if ($isNewPhrase) {
            $phraseWords = array_unique($request->words);
            $lessons = Lesson
                ::where('user_id', Auth::user()->id)
                ->where('language', $selectedLanguage)
                ->get();

            foreach ($lessons as $lesson) {
                $uniqueWords = json_decode($lesson->unique_words);
                if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                    continue;
                }

                $words = LessonWord
                    ::where('user_id', Auth::user()->id)
                    ->where('lesson_id', $lesson->id)
                    ->get();
                    
                foreach ($words as $word) {
                    $word->phrase_ids = json_decode($word->phrase_ids);
                }

                $textBlock = new TextBlock();
                $textBlock->setProcessedWords($words);
                $textBlock->collectUniqueWords();
                $textBlock->updatePhraseIds($phrase);

                // save lesson words
                DB::beginTransaction();
                foreach ($textBlock->processedWords as $word) {
                    $word->phrase_ids = json_encode($word->phrase_ids);
                    $word->save();
                }

                DB::commit();
            }
        }

        // update phrase ids in example sentences
        if ($isNewPhrase) {
            $exampleSentences = ExampleSentence
                ::where('user_id', Auth::user()->id)
                ->where('language', $selectedLanguage)
                ->get();

            DB::beginTransaction();
            foreach ($exampleSentences as $exampleSentence) {
                $uniqueWords = json_decode($exampleSentence->unique_words);
                if (count(array_intersect($uniqueWords, $phraseWords)) !== count($phraseWords)) {
                    continue;
                }

                $textBlock = new TextBlock();
                $textBlock->setProcessedWords(json_decode($exampleSentence->words));
                $textBlock->collectUniqueWords();
                $textBlock->updatePhraseIds($phrase);
                $textBlock->createNewEncounteredWords();
                
                $exampleSentence->words = json_encode($textBlock->processedWords);
                $exampleSentence->unique_words = json_encode($textBlock->uniqueWords);
                $exampleSentence->save();
            }

            DB::commit();
        }

        return $phrase->id;
    }

    public function deletePhrase(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $phraseId = $request->id;
        $lessonIds = Lesson
            ::select('id')
            ->where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->pluck('id')
            ->toArray();
            
        $words = LessonWord
            ::where('user_id', Auth::user()->id)
            ->where('phrase_ids', '<>', '[]')
            ->whereIn('lesson_id', $lessonIds)
            ->get();

        // delete phrase id from lesson words
        foreach ($words as $word) {
            $word->phrase_ids = json_decode($word->phrase_ids);
            $index = array_search($phraseId, $word->phrase_ids);
            if ($index !== false) {
                $modifiedPhraseIds = $word->phrase_ids;
                array_splice($modifiedPhraseIds, $index, 1);
                $word->phrase_ids = json_encode($modifiedPhraseIds);
                $word->save();
            }
        }

        // delete phrase id from example sentences
        $exampleSentences = ExampleSentence
            ::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->get();

        DB::beginTransaction();
        foreach ($exampleSentences as $exampleSentence) {
            $exampleSentence->deletePhraseId($phraseId);
        }

        DB::commit();
        
        ExampleSentence
            ::where('user_id', Auth::user()->id)
            ->where('target_type', 'phrase')
            ->where('target_id', $request->id)
            ->delete();

        Phrase
            ::where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->where('id', $request->id)
            ->delete();
    }

    public function getExampleSentence($targetId, $targetType) {
        $exampleSentence = ExampleSentence
            ::where('user_id', Auth::user()->id)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->first();
        
        if ($exampleSentence) {
            $textBlock = new TextBlock();
            $textBlock->setProcessedWords(json_decode($exampleSentence->words));
            $textBlock->uniqueWords = json_decode($exampleSentence->unique_words);
            $textBlock->prepareTextForReader();
            $textBlock->indexPhrases();

            return $textBlock->getReaderData();
        } else {
            return 'no example sentence';
        }
        
    }

    public function saveExampleSentence(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $userId = Auth::user()->id;
        $targetType = $request->targetType;
        $targetId = $request->targetId;
        $exampleSentenceWords = $request->exampleSentenceWords;

        // Retrieve example sentence.
        $exampleSentence = ExampleSentence
            ::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->first();


        // Create new example sentence record if it didn't exist, and update words.
        $exampleSentenceWords = json_decode($exampleSentenceWords);
        if (!$exampleSentence) {
            $exampleSentence = new ExampleSentence();
            $exampleSentence->user_id = $userId;
            $exampleSentence->language = $selectedLanguage;
            $exampleSentence->target_type = $targetType;
            $exampleSentence->target_id = $targetId;
            $exampleSentence->unique_words = [];
        }

        // Update unique words.
        $uniqueWords = [];
        foreach ($exampleSentenceWords as $word) {
            $lowerCaseWord = mb_strtolower($word->word, 'UTF-8');
            if (!in_array($lowerCaseWord, $uniqueWords, true)) {
                array_push($uniqueWords, $lowerCaseWord);
            }
        }
        
        $textBlock = new TextBlock();
        $textBlock->setProcessedWords($exampleSentenceWords);
        $textBlock->collectUniqueWords();
        $textBlock->updateAllPhraseIds();

        // Save example sentence.
        $exampleSentence->words = json_encode($textBlock->processedWords);
        $exampleSentence->unique_words = json_encode($textBlock->uniqueWords);
        $exampleSentence->save();
    }


    public function exportToCsv(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $text = $request->post('text');
        $bookId = $request->post('book');
        $chapterId = $request->post('chapter');
        $stage = $request->post('stage');
        $phrases = $request->post('phrases');
        $orderBy = $request->post('orderBy');
        $translation = $request->post('translation');
        $fields = $request->post('fields');

        // get books and chapters
        $books = Book::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        $bookIndex = -1;
        for ($i = 0; $i < count($books); $i++) {
            $books[$i]->chapters = Lesson::select(['id', 'name'])->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('book_id', $books[$i]->id)->get();
            
            if (isset($bookId) && $books[$i]->id == $bookId) {
                $bookIndex = $i;
            }
        }

        $words = $this->buildSearchRequest($text, $books, $bookIndex, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation)->get();

        // create csv file
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setDelimiter('|');


        // insert headers to csv
        $csvArray = [];
        foreach ($fields as $field) {
            if ($field['export']) {
                $csvArray[] = $field['headerName'];
            }
        }
        
        $csv->insertOne($csvArray);

        // insert data to csv
        foreach($words as $word) {
            $csvArray = [];
            foreach ($fields as $field) {
                if ($field['export']) {
                    $searchObjectProperty = $field['searchObjectProperty'];
                    $csvArray[] = $word->$searchObjectProperty;
                }
            }
            
            $csv->insertOne($csvArray);
        }

        $csv->output('vocabulary.csv');
        return;
    }

    public function search(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $text = $request->text;
        $bookId = $request->book;
        $chapterId = $request->chapter;
        $stage = $request->stage;
        $phrases = $request->phrases;
        $orderBy = $request->orderBy;
        $translation = $request->translation;
        $page = $request->page;

        // get books and chapters
        $books = Book::where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->get();
        $bookIndex = -1;
        for ($i = 0; $i < count($books); $i++) {
            $books[$i]->chapters = Lesson::select(['id', 'name'])->where('user_id', Auth::user()->id)->where('language', $selectedLanguage)->where('book_id', $books[$i]->id)->get();
            
            if (isset($bookId) && $books[$i]->id == $bookId) {
                $bookIndex = $i;
            }
        }

        $search = $this->buildSearchRequest($text, $books, $bookIndex, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation);

        $data = new \stdClass();
        $data->wordCount = $search->count();
        $data->words = $search->skip(($page - 1) * $this->limit)->take($this->limit)->get();
        $data->books = $books;
        $data->bookIndex = $bookIndex;
        $data->pageCount = ceil($data->wordCount / $this->limit);
        $data->currentPage = $page;

        return json_encode($data);
    }

    /*
        Builds a search request. It's used for both searching and exporting vocabulary.
    */
    private function buildSearchRequest($text, $books, $bookIndex, $bookId, $chapterId, $stage, $phrases, $orderBy, $translation) {
        $wordsToSkip = config('langapp.wordsToSkip');
        $selectedLanguage = Auth::user()->selected_language;
        $results = [];

        // get words and phrases
        // from filtered chapters
        $filteredChapters = Lesson::where('user_id', Auth::user()->id)->where('language', $selectedLanguage);
        $filteredWords = [];
        $filteredPhraseIds = [];
        if ($bookId !== 'any') {
            $filteredChapters = $filteredChapters->where('book_id', $bookId);
        }

        if ($chapterId !== 'any') {
            $filteredChapters = $filteredChapters->where('id', $chapterId);
        }
        
        $filteredChapters = $filteredChapters->get();

        if ($bookId !== 'any') {
            foreach ($filteredChapters as $filteredChapter) {
                // add filtered phrase ids
                $filteredChapterText = LessonWord
                ::where('user_id', Auth::user()->id)
                ->where('lesson_id', $filteredChapter->id)
                ->get();

                foreach ($filteredChapterText as $filteredChapterWord) {
                    $filteredChapterWord->phrase_ids = json_decode($filteredChapterWord->phrase_ids);
                    foreach ($filteredChapterWord->phrase_ids as $phraseId) {
                        if (!in_array($phraseId, $filteredPhraseIds, true)) {
                            array_push($filteredPhraseIds, $phraseId);
                        }
                    }
                }

                // add filtered words
                $filteredChapterUniqueWords = json_decode($filteredChapter->unique_words);
                foreach ($filteredChapterUniqueWords as $filteredChapterUniqueWord) {
                    if (!in_array($filteredChapterUniqueWord, $filteredWords, true)) {
                        array_push($filteredWords, $filteredChapterUniqueWord);
                    }
                }
            }
        }

        // search for words and apply filters
        $wordSearch = EncounteredWord
            ::select('id', 'base_word', 'word', 'reading', 'base_word_reading', 'stage', 'translation', 'read_count', 'lookup_count', 'added_to_srs', DB::raw("'word' AS type"))->where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage)
            ->whereNotIn('word', $wordsToSkip);

        if ($text !== 'anytext') {
            $wordSearch = $wordSearch->where(function($query) use ($text) {
                $query->orWhere('word', 'like', '%' . $text . '%')
                    ->orWhere('reading', 'like', '%' . $text . '%');
            });
        }

        if ($bookId !== 'any') {
            $wordSearch->whereIn('word', $filteredWords);
        }

        if ($stage !== 'any') {
            $wordSearch = $wordSearch->where('stage', $stage);
        }

        if ($translation == 'not empty') {
            $wordSearch = $wordSearch->where('translation', '<>', '');
        }
        
        // search for phrases and apply filters
        $phraseSearch = Phrase
            ::select('id', DB::raw("'' AS base_word"), 'words_searchable as word', 'reading', DB::raw("'' AS base_word_reading"), 'stage', 'translation', DB::raw("-1 AS read_count"), DB::raw("-1 AS lookup_count"), 'added_to_srs', DB::raw("'phrase' AS type"))
            ->where('user_id', Auth::user()->id)
            ->where('language', $selectedLanguage);

        if ($text !== 'anytext') {
            $phraseSearch = $phraseSearch->where(function($query) use ($text) {
                $query->orWhere('words_searchable', 'like', '%' . $text . '%')
                    ->orWhere('reading', 'like', '%' . $text . '%');
            });
        }

        if ($bookId !== 'any') {
            $phraseSearch->whereIn('id', $filteredPhraseIds);
        }

        if ($stage !== 'any') {
            $phraseSearch = $phraseSearch->where('stage', $stage);
        }

        if ($translation == 'not empty') {
            $phraseSearch = $phraseSearch->where('translation', '<>', '');
        }

        if ($phrases == 'only words') {
            $search = $wordSearch;
        } else if ($phrases == 'only phrases') {
            $search = $phraseSearch;
        } else {  
            $search = $wordSearch->union($phraseSearch);
        }

        if ($orderBy == 'words') {
            $search = $search->orderBy('word');
        }

        if ($orderBy == 'words desc') {
            $search = $search->orderBy('word', 'desc');
        }

        if ($orderBy == 'stage') {
            $search = $search->orderBy('stage');
        }

        if ($orderBy == 'stage desc') {
            $search = $search->orderBy('stage', 'desc');
        }

        return $search;
    }

    public function searchKanji(Request $request) {
        $selectedLanguage = Auth::user()->selected_language;
        $words = EncounteredWord::where('user_id', Auth::user()->id)->where('stage', 0)->where('language', $selectedLanguage)->where('kanji', '<>', '')->get();
        
        // get knwon kanji
        $knownKanji = [];
        foreach ($words as $word) {
            $wordKanji = preg_split("//u", $word->kanji, -1, PREG_SPLIT_NO_EMPTY);
            foreach($wordKanji as $currentKanji) {
                if(!in_array($currentKanji, $knownKanji, true)) {
                    array_push($knownKanji, $currentKanji);
                }
            }
        }

        // get kanji list
        if ($request->groupBy == 'grade') {
            $kanji = Kanji::where(function($query) use($knownKanji) {
                $query->where('grade', '>', 0)->orWhereIn('kanji', $knownKanji);
            });
        } else {
            $kanji = Kanji::where(function($query) use($knownKanji) {
                $query->where('jlpt', '>', 0)->orWhereIn('kanji', $knownKanji);
            });
        }

        if (!$request->showUnknown) {
            $kanji = $kanji->whereIn('kanji', $knownKanji);
        }
        
        $kanji = $kanji->get();

        // label kanji list
        foreach ($kanji as $currentKanji) {
            $currentKanji->known = in_array($currentKanji->kanji, $knownKanji);
        }

        // group kanji list
        if ($request->groupBy == 'grade') {
            $kanji = $kanji->groupBy('grade');
        } else {
            $kanji = $kanji->groupBy('jlpt');
        }
        

        // get count for statistics
        if ($request->groupBy == 'grade') {
            $totalCount = Kanji::select('grade', DB::raw('count(id) as total'))->groupBy('grade')->get()->keyBy('grade');
            $knownCount = Kanji::select('grade', DB::raw('count(id) as total'))->whereIn('kanji', $knownKanji)->groupBy('grade')->get()->keyBy('grade');
        } else {
            $totalCount = Kanji::select('jlpt', DB::raw('count(id) as total'))->groupBy('jlpt')->get()->keyBy('jlpt');
            $knownCount = Kanji::select('jlpt', DB::raw('count(id) as total'))->whereIn('kanji', $knownKanji)->groupBy('jlpt')->get()->keyBy('jlpt');
        }
        
        $data = new \stdClass();
        $data->kanji = $kanji;
        $data->total = $totalCount;
        $data->known = $knownCount;

        return json_encode($data);
    }

    public function getKanjiDetails(Request $request) {
        $kanji = Kanji::where('kanji', $request->kanji)->first();
        $words = EncounteredWord::where('word', 'like', '%' . $request->kanji . '%')->limit(12)->get();
        $radicals = Radical::select('radicals')->where('kanji', $request->kanji)->first();
        
        $data = new \stdClass();
        $data->kanji = $kanji;
        $data->radicals = $radicals->radicals;
        $data->words = $words;

        return json_encode($data);
    }
}
